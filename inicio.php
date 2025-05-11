<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesion</title>
</head>
<style>

body {
    margin: 0; 
    background-color: beige;
}   

.iniciar-sesion{
    background-color: navy; 
    margin-left: auto; 
    margin-right: auto;
    width: 500px;  
    height: 400px; 
    margin-top: 100px; 
    border-radius: 20px;    
    padding: 20px;
    color:white;
} 

input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            width: 450px;
        }

        button {
            padding: 10px;
            margin-top: 15px;
            width: 300px;
            background-color: white;
            color: navy;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        h2 {
            text-align: center;
        }

</style>
<body>
    <div class="iniciar-sesion">
        
    <h2>Iniciar sesion</h2> 
<form action="consultas.php" method="POST">
        <label>Nombre de usuario</label>  
        <input type="text" name="nombre_usuario" id="nombre_usuario" required><br><br> 

        <label>Contraseña</label> 
        <input type="password" name="contrasena_usuario" id="contrasena_usuario" required><br><br>
        <button type="submit">Iniciar sesion</button>
    </form>
    </div>
</body>
</html>