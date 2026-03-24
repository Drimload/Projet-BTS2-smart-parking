const express = require('express')
const session = require('express-session')
require('dotenv').config()

const app = express()

app.set('view engine','ejs')
app.set('views','./views')

app.use(express.static('public'))
app.use(express.urlencoded({ extended: true }))

// Permet de lire les données envoyées en format JSON
// Utile pour les échanges entre le JS front-end et le serveur
app.use(express.json())

app.use(session({
  secret: process.env.SESSION_SECRET || 'secret-parking',
  // secret = clé pour signer les cookies, lu depuis .env

  resave: false,
  // false = ne sauvegarde pas la session si elle n'a pas changé

  saveUninitialized: false,
  // false = ne crée pas de session pour les visiteurs non connectés

  cookie: { maxAge: 3600000 }
  // durée de vie du cookie : 3600000ms = 1 heure
  // après 1h sans activité → déconnexion automatique
}))


// ============================================================
// CONNEXION BASE DE DONNÉES
// On charge le fichier config/db.js
// Il crée le pool de connexions PostgreSQL
// et teste la connexion au démarrage
// ============================================================
const db = require('./config/db')

// ============================================================
// ROUTE PROVISOIRE
// Juste pou
app.get('/', (req, res) => {
  res.send(`
    <h1>Smart Parking CREPS</h1>
    <p>✅ Serveur en marche</p>
    <p>Base de données : ${process.env.DB_HOST}</p>
  `)
})


// ============================================================
// DÉMARRAGE DU SERVEUR
// app.listen() démarre le serveur sur le port 3000
// Ce port est mappé sur le port 80 dans docker-compose
// donc accessible via http://100.118.69.115 sans numéro de port
// ============================================================
const PORT = 3000
app.listen(PORT, () => {
  console.log(`✅ Serveur Smart Parking démarré sur le port ${PORT}`)
})
