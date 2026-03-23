const express = require('express')
const session = require('express-session')
require('dotenv').config()

const app = express()

app.set('view engine','ejs')
app.set('views','./views')


