const express = require('express');
const bodyParser = require('body-parser');
const multer = require('multer');
const path = require('path');
const app = express();

let pendingLenders = [];
let acceptedLenders = [];
let declinedLenders = [];


app.use(express.static('public'));
app.use('/uploads', express.static('uploads'));


const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, 'uploads/'); 
    },
    filename: function (req, file, cb) {
        cb(null, Date.now() + path.extname(file.originalname)); 
    }
});
const upload = multer({ storage: storage });

app.use(bodyParser.json());


app.post('/api/register-lender', upload.single('image'), (req, res) => {
    const { name, email } = req.body;
    const image = req.file ? `/uploads/${req.file.filename}` : null; 
    const id = pendingLenders.length + 1;
    pendingLenders.push({ id, name, email, image, registrationDate: new Date().toISOString() });
    
    res.json({ message: 'Lender registered, waiting for admin approval', id, name, email, image });
});

// Get pending lenders
app.get('/api/pending-lenders', (req, res) => {
    res.json(pendingLenders);
});

// Get accepted lenders
app.get('/api/accepted-lenders', (req, res) => {
    res.json(acceptedLenders);
});

// Get declined lenders
app.get('/api/declined-lenders', (req, res) => {
    res.json(declinedLenders);
});

// Accept lender
app.post('/api/accept-lender', (req, res) => {
    const lenderId = parseInt(req.body.id);
    const lender = pendingLenders.find(l => l.id === lenderId);
    
    if (lender) {
        acceptedLenders.push(lender);
        pendingLenders = pendingLenders.filter(l => l.id !== lenderId);
        res.json({ message: 'Lender accepted', lender });
    } else {
        res.status(404).json({ message: 'Lender not found' });
    }
});

// Decline lender
app.post('/api/decline-lender', (req, res) => {
    const lenderId = parseInt(req.body.id);
    const lender = pendingLenders.find(l => l.id === lenderId);
    
    if (lender) {
        declinedLenders.push(lender);
        pendingLenders = pendingLenders.filter(l => l.id !== lenderId);
        res.json({ message: 'Lender declined', lender });
    } else {
        res.status(404).json({ message: 'Lender not found' });
    }
});


const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});
