#!/usr/bin/env python3
"""
mqtt_listener.py - Smart Parking
Subscribe au topic ChirpStack et insère les données en PostgreSQL
BTS CIEL IR - 2026
"""

import json
import os
import time
import logging
import paho.mqtt.client as mqtt
import psycopg2
import psycopg2.extras


logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(message)s'
)
log = logging.getLogger(__name__)


MQTT_HOST  = os.getenv('MQTT_HOST', 'mqtt')
MQTT_PORT  = int(os.getenv('MQTT_PORT', 1883))
MQTT_TOPIC = os.getenv('MQTT_TOPIC', 'application/#')

DB_HOST     = os.getenv('DB_HOST', 'db')
DB_PORT     = os.getenv('DB_PORT', '5432')
DB_NAME     = os.getenv('DB_NAME', 'parking_db')
DB_USER     = os.getenv('DB_USER', 'admin')
DB_PASSWORD = os.getenv('DB_PASSWORD', 'Password123')


conn = None


def ensure_db():
    global conn
    try:
        if conn is None or conn.closed:
            conn = psycopg2.connect(
                host=DB_HOST, port=DB_PORT,
                dbname=DB_NAME, user=DB_USER, password=DB_PASSWORD
            )
            log.info("Connexion PostgreSQL établie")
    except Exception as e:
        log.error(f"Erreur connexion BDD : {e}")
        conn = None


def on_connect(client, userdata, flags, reason_code, properties):
    if reason_code == 0:
        log.info(f"Connecté au broker MQTT ({MQTT_HOST}:{MQTT_PORT})")
        client.subscribe(MQTT_TOPIC)
        log.info(f"Subscribed à : {MQTT_TOPIC}")
    else:
        log.error(f"Echec connexion MQTT, code: {reason_code}")


def on_disconnect(client, userdata, flags, reason_code, properties):
    log.warning(f"Déconnecté du broker MQTT (code: {reason_code}), reconnexion...")


def on_message(client, userdata, msg):
    global conn

    try:
        payload = json.loads(msg.payload.decode())
    except Exception:
        log.warning("Payload non JSON ignoré")
        return

    obj      = payload.get('object', {})
    dev_info = payload.get('deviceInfo', {})
    dev_eui  = dev_info.get('devEui', '').lower()
    time_val = payload.get('time')

    parking_status  = obj.get('parking_status')
    is_keepalive    = obj.get('is_keepalive', False)
    battery_percent = obj.get('battery_percent')

    if not parking_status and not is_keepalive:
        log.debug(f"[{dev_eui}] Pas de parking_status ni keepalive, ignoré")
        return

    ensure_db()
    if conn is None:
        log.error("Pas de connexion BDD, message perdu")
        return

    try:
        cur = conn.cursor(cursor_factory=psycopg2.extras.RealDictCursor)

        cur.execute(
            "SELECT id_capteur, id_place FROM capteur WHERE LOWER(dev_eui) = %s LIMIT 1",
            (dev_eui,)
        )
        capteur = cur.fetchone()

        if not capteur:
            log.warning(f"Capteur introuvable en BDD pour dev_eui: {dev_eui}")
            cur.close()
            return

        id_capteur = capteur['id_capteur']
        id_place   = capteur['id_place']

        if is_keepalive:
            etat_occupation = (parking_status == 'BUSY') if parking_status else None
            log.info(f"[{dev_eui}] KEEP-ALIVE | batterie: {battery_percent}% | état: {parking_status}")

            if battery_percent is not None:
                cur.execute(
                    "UPDATE capteur SET niveau_batterie = %s, statut = true, last_seen_at = NOW() WHERE id_capteur = %s",
                    (int(battery_percent), id_capteur)
                )
            else:
                cur.execute(
                    "UPDATE capteur SET statut = true, last_seen_at = NOW() WHERE id_capteur = %s",
                    (id_capteur,)
                )

            cur.execute(
                """INSERT INTO mesure_capteur
                       (date_heure, etat_occupation, id_capteur, type_trame, niveau_batterie)
                   VALUES (NOW(), %s, %s, 'keepalive', %s)""",
                (etat_occupation, id_capteur, int(battery_percent) if battery_percent is not None else None)
            )

            if id_place and etat_occupation is not None:
                cur.execute(
                    "UPDATE place SET etat = %s WHERE id_place = %s",
                    ('occupee' if etat_occupation else 'libre', id_place)
                )

        else:
            etat_occupation = (parking_status == 'BUSY')
            log.info(f"[{dev_eui}] ÉVÉNEMENT {parking_status}")

            cur.execute(
                """INSERT INTO mesure_capteur
                       (date_heure, etat_occupation, id_capteur, type_trame)
                   VALUES (%s, %s, %s, 'detection')""",
                (time_val, etat_occupation, id_capteur)
            )

            if id_place:
                cur.execute(
                    "UPDATE place SET etat = %s WHERE id_place = %s",
                    ('occupee' if etat_occupation else 'libre', id_place)
                )

            cur.execute(
                "UPDATE capteur SET statut = true, last_seen_at = NOW() WHERE id_capteur = %s",
                (id_capteur,)
            )

        conn.commit()
        cur.close()
        log.info(f"[{dev_eui}] BDD mise à jour OK")

    except Exception as e:
        log.error(f"Erreur BDD : {e}")
        conn.rollback()


if __name__ == '__main__':
    ensure_db()

    client = mqtt.Client(
        callback_api_version=mqtt.CallbackAPIVersion.VERSION2,
        client_id="smart-parking-listener"
    )
    client.on_connect    = on_connect
    client.on_message    = on_message
    client.on_disconnect = on_disconnect
    client.reconnect_delay_set(min_delay=5, max_delay=30)

    while True:
        try:
            client.connect(MQTT_HOST, MQTT_PORT, keepalive=60)
            client.loop_forever()
        except Exception as e:
            log.error(f"Erreur connexion MQTT : {e}, retry dans 10s...")
            time.sleep(10)