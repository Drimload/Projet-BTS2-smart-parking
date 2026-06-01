FROM python:3.11-slim
WORKDIR /app
RUN pip install --no-cache-dir paho-mqtt psycopg2-binary
COPY mqtt_listener.py .
CMD ["python", "-u", "mqtt_listener.py"]