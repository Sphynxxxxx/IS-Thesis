<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selection</title>
<style>
    body {
        margin: 0;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #f4f4f4;
        background-image: url('index/css/images/Pototan_hall_wide.jpg');
        background-size: cover;
        background-position: center;
        position: relative;
        background-repeat: no-repeat;
    }

    body::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('css/images/Pototan_hall_wide.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        filter: blur(3px); 
        z-index: -1; 
    }


    .container {
        display: flex; 
        flex-direction: column; 
        justify-content: center; 
        align-items: center; 
    }

    h1 {
        font-size: 50px; 
        text-align: center; 
        margin-bottom: 20px; 
        padding: 0 10px; 
        color: #2F5233; 
    }


    .btn {
        display: flex; 
        justify-content: center; 
        align-items: center;
        width: 200px;
        padding: 10px 20px;
        margin: 10px;
        background-color: #2F5233;
        color: white;
        text-decoration: none;
        font-size: 18px;
        border-radius: 5px;
        transition: background-color 0.3s;
    }

    .btn:hover {
        background-color: rgba(76, 175, 80, 1);
    }

    


</style>
</head>
<body>

<div class="container">
    <h1>Shop and Rent the Farming Tools and Equipment</h1>
    <a href="index/CustomerMain.php" class="btn">Get Started</a>
</div>

</body>
</html>
