const express = require('express')
const app = express()

app.get('/', (req, res) => {


  res.send('
    <h1>Smart Parking CREPS</h1>
    <p>Bienvenue sur le tableau de bord</p>
    ')
    
})

app.listen(3000, () => {
  console.log('Serveur démarré sur le port 3000')
})