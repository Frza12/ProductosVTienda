<?php

session_start();


$db_server = "localhost";
$db_user = "root";
$db_pass = "franrg812";
$db_name = "inventario";

$con = mysqli_connect($db_server, $db_user, $db_pass, $db_name);

if (!$con) {
    die("No se pudo conectar a la base de datos");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contrasena'];

    $sql_login = "SELECT * FROM usuarios WHERE nombre_usuario = ? AND contrasena = ?";
    $stmt = mysqli_prepare($con, $sql_login);
    mysqli_stmt_bind_param($stmt, "ss", $usuario, $contrasena);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['usuario'] = $usuario;
    } else {
        echo "<script>alert('Credenciales incorrectas'); window.location.href = 'index.php';</script>";
        exit;
    }
}

if (!isset($_SESSION['usuario'])) {
    echo "<script>alert('Debe iniciar sesión'); window.location.href = 'index.php';</script>";
    exit;
}

$usuario = $_SESSION['usuario'];

$productos = [];
$categoriaSeleccionada = $_GET['categoria'] ?? '';
$vistaOrdenada = $_GET['ordenNombre'] ?? '';
$vistaDetalles = $_GET['detalles'] ?? '';
$conteoCategorias = [];
$productosVendidos = $_GET['ventas'] ?? '';
$informacionVentas = $_GET['ventasInfo'] ?? '';
$pedidos = [];
$vistaTotal = $_GET['precioTotal'] ?? '';
$productos10 = $_GET['10p'] ?? '';
$productos1 = $_GET['1p'] ?? '';
$precioMin = $_GET['min'] ?? '';
$precioMax = $_GET['max'] ?? '';
$electronicosMax = $_GET['eMax'] ?? '';
$mejorPreDis = $_GET['mejorPrecio'] ?? '';
$productosCategorias = $_GET['categoriaprecio'] ?? '';

if ($con) {
    $sqlConteo = "SELECT categoria, COUNT(*) AS total FROM productos GROUP BY categoria";
    $resultadoConteo = mysqli_query($con, $sqlConteo);
    if ($resultadoConteo) {
        while ($fila = mysqli_fetch_assoc($resultadoConteo)) {
            $conteoCategorias[$fila['categoria']] = $fila['total'];
        }
    }
    /*filtrar por categoria*/
    if ($categoriaSeleccionada) {
        $sql = "SELECT nombre_producto, precio, cantidad_inventario, categoria FROM productos WHERE categoria = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "s", $categoriaSeleccionada);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
    }
    /*vista ascendente*/ elseif ($vistaOrdenada === 'ascendienteNombre') {
        $sql = "SELECT * FROM vista_inventario_ordenada_alfabeticamente";
        $resultado = mysqli_query($con, $sql);

    }
    /*vista descendiente*/ elseif ($vistaOrdenada === 'descendienteNombre') {
        $sql = "SELECT * FROM vista_inventario_ordenada_desc";
        $resultado = mysqli_query($con, $sql);
    }
    /*detalles productos*/ elseif ($vistaDetalles === 'detallesProducto') {
        $sql = "SELECT * FROM vista_productos_detalles";
        $resultado = mysqli_query($con, $sql);
    }
    /*Productos vendidos*/ elseif ($productosVendidos === 'cantidadVendida') {
        $sql = "SELECT * FROM vista_productos_vendidos";
        $resultado = mysqli_query($con, $sql);
    }
    /*Todos los productos vendidos*/ elseif ($informacionVentas === 'todasVentas') {
        $sql = " SELECT * FROM vista_productos_vendidos_completa";
        $resultado = mysqli_query($con, $sql);
    }
    /*Productos mas vendidos*/ elseif ($productos10 === 'vendidos10') {
        $sql = "SELECT * FROM vista_productos_mas_vendidos";
        $resultado = mysqli_query($con, $sql);
    }
    /*Productos mas solicitados*/ elseif ($productos10 === 'vendidos1') {
        $sql = "SELECT * FROM vista_productos_mas_solicitados";
        $resultado = mysqli_query($con, $sql);
    }
    /*Productos mas baratos*/ elseif ($precioMin === 'Min') {
        $sql = "SELECT * FROM vista_productos_mas_baratos";
        $resultado = mysqli_query($con, $sql);
    }
    /*Productos mas caros*/ elseif ($precioMax = 'Max') {
        $sql = "SELECT * FROM vista_productos_mas_caros";
        $resultado = mysqli_query($con, $sql);
    }
    /*Electronicos */ elseif ($electronicosMax === 'electronicosMax') {
        $sql = "SELECT * FROM vista_productos_electronicos";
        $resultado = mysqli_query($con, $sql);
    }
    /*Mejor precio*/ elseif ($mejorPreDis === 'mejorPrecio') {
        $sql = "SELECT * FROM vista_productos_mejor_precio";
        $resultado = mysqli_query($con, $sql);
    }
    /*Valoracion precio*/ elseif ($productosCategorias === 'categoriaPrecio') {
        $sql = "SELECT * FROM vista_clasificacion_precio";
        $resultado = mysqli_query($con, $sql);
    } else {
        $sql = "SELECT * FROM Productos";
        $resultado = mysqli_query($con, $sql);
    }

    if ($resultado) {

        while ($fila = mysqli_fetch_assoc($resultado)) {
            $productos[] = $fila;
        }
    }

    mysqli_close($con);

    try {
        $con2 = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
    } catch (mysqli_sql_exception) {
        echo "No se pudo conectar";

    }

    if ($con2) {
        $sql2 = "SELECT * FROM vista_total_pedidos";
        $resultado2 = mysqli_query($con2, $sql2);

        if ($vistaTotal == "totalPorPedido") {
            $sql2 = "SELECT * FROM vista_total_pedidos";
            mysqli_query($con2, $sql2);
        }

        if ($resultado2) {


            while ($fila = mysqli_fetch_assoc($resultado2)) {
                $pedidos[] = $fila;
            }
        }
    }

    mysqli_close($con2);

}


$productosPorPagina = 10;
$paginaActual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$paginaActual = max($paginaActual, 1);
$inicio = ($paginaActual - 1) * $productosPorPagina;
$productosPaginados = array_slice($productos, $inicio, $productosPorPagina);
$totalProductos = count($productos);
$totalPaginas = ceil($totalProductos / $productosPorPagina); 
$params = $_GET;
unset($params['pagina']); 
$queryString = http_build_query($params);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultas</title>
</head>

<style>
    body {
        margin: 0;
        background-color: beige;
    }

    .bloque-superior {
        width: 100%;
        height: 50px;
        background-color: navy;
    }

    .titulo {
        text-align: center;
    }

    .consultas {
        background-color: gainsboro;
        width: 800px;
        height: 100px;
        margin-right: auto;
        margin-left: auto;
        border-radius: 5px;

    }

    .consultas form {
        display: inline-block;
    }

    .contenedor-lista {
        margin-top: 30px;
        background-color: gainsboro;
        width: 700px;
        height: 500px;
        margin-right: auto;
        margin-left: auto;
        padding: 50px 20px 100px 70px;
        border-radius: 10px;
    }

    .boton-c1,
    .boton-c2,
    .boton-c3,
    .boton-c4,
    .boton-c5,
    .boton-c6,
    .boton-c7,
    .boton-c8,
    .boton-c9,
    .boton-c10,
    .boton-c11,
    .boton-c12 {
        margin-left: 5px;
        margin-top: 10px;
        background-color: whitesmoke;
        font-size: 14px;
        border-radius: 4px;

    }


    .lista-categorias {
        margin-left: 5px;
        margin-top: 10px;
        font-size: 14px;
        background-color: whitesmoke;

    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }

    th,
    td {
        padding: 10px;
        text-align: center;
        word-break: break-word;
    } 

    .paginacion {
    text-align: center;
    margin-top: 20px;
    }

    .numero-pagina {
    display: inline-block;
    padding: 8px 12px;
    margin: 0 4px;
    background-color: whitesmoke;
    color: navy;
    border: 1px solid navy;
    border-radius: 50%;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s, color 0.3s;
}

.numero-pagina:hover {
    background-color: navy;
    color: white;
}

.numero-pagina.activo {
    background-color: navy;
    color: white;
}

p{
    color: white; 
    text-align: right;
    margin-right: 20px; 
    line-height: 50px;
}

a{
    color: white; 
    text-decoration: underline;
}

</style>

<body>
    <div class="bloque-superior"> 
        <p>
       Usuario: <?= htmlspecialchars($usuario) ?>
        <a href="index.php">Volver al inicio de sesión</a>
        </p>
    </div>
    <h2 class="titulo">Listado</h2>
    <div class="consultas">
        <form method="GET" action="">
            <button class="boton-c1" name="ordenNombre" value="ascendienteNombre">Ordenar por nombre (ASC)</button>
        </form>
        <form method="GET" action="">
            <button class="boton-c2" name="ordenNombre" value="descendienteNombre">Ordenar por nombre (DESC)</button>
        </form>
        <form method="GET" action="">
            <button class="boton-c3" name="detalles" value="detallesProducto">Mostrar descripcion</button>
        </form>
        <form method="GET" action="">
            <button class="boton-c4" name="ventas" value="cantidadVendida">Productos vendidos</button>
        </form>
        <form method="GET" action="">
            <button class="boton-c5" name="ventasInfo" value="todasVentas">Productos vendidos</button>
        </form>
        <form method="GET" action="">
            <button class="boton-c7" name="10p" value="vendidos10">Productos con mayor numero de ventas</button>
        </form>
        <form method="GET" action="">
            <button class="boton-c8" name="1p" value="vendidos1">Productos mas solicitados</button>
        </form>
        <form method="GET" action="">
            <button class="boton-c9" name="min" value="Min">Productos mas baratos</button>
        </form>
        <form method="GET" action="">
            <button class="boton-c10" name="max" value="Max">Productos mas caros</button>
        </form>
        <form method="GET" action="">
            <button class="boton-c10" name="eMax" value="electronicosMax">Electronicos con mayor disponibilidad</button>
        </form>
        <form method="GET" action="">
            <button class="boton-c11" name="mejorprecio" value="mejorPrecio">Mejor precio y disponibilidad</button>
        </form>
        <form method="GET" action="">
            <button class="boton-c12" name="categoriaprecio" value="categoriaPrecio">Categoria precio</button>
        </form>

        <form method="GET" action="">
            <select name="categoria" class="lista-categorias" onchange="this.form.submit()">
                <option value="">-- Todas --</option>
                <option value="Electrodomesticos" <?= $categoriaSeleccionada == 'Electrodomesticos' ? 'selected' : '' ?>>
                    Electrodomesticos (<?= $conteoCategorias['Electrodomesticos'] ?? 0 ?>)</option>
                <option value="Electronica" <?= $categoriaSeleccionada == 'Electronica' ? 'selected' : '' ?>>Electronica
                    (<?= $conteoCategorias['Electronica'] ?? 0 ?>)</option>
                <option value="Ropa" <?= $categoriaSeleccionada == 'Ropa' ? 'selected' : '' ?>> Ropa
                    (<?= $conteoCategorias['Ropa'] ?? 0 ?>)</option>
                <option value="Mobiliario" <?= $categoriaSeleccionada == 'Mobiliario' ? 'selected' : '' ?>>Mobiliario
                    (<?= $conteoCategorias['Mobiliario'] ?? 0 ?>)</option>
                <option value="Papeleria" <?= $categoriaSeleccionada == 'Papeleria' ? 'selected' : '' ?>>Papeleria
                    (<?= $conteoCategorias['Papeleria'] ?? 0 ?>)</option>
                <option value="Oficina" <?= $categoriaSeleccionada == 'Oficina' ? 'selected' : '' ?>>Oficina
                    (<?= $conteoCategorias['Oficina'] ?? 0 ?>)</option>
                <option value="Decoracion" <?= $categoriaSeleccionada == 'Decoracion' ? 'selected' : '' ?>>Decoracion
                    (<?= $conteoCategorias['Decoracion'] ?? 0 ?>)</option>
            </select>
        </form>


    </div>


    <div class="contenedor-lista">
        <?php if (!empty($productos)): ?>
            <table border="1">
                <tr>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Categoría</th>
                    <?php if ($productosVendidos === 'cantidadVendida' or $informacionVentas === "todasVentas"): ?>
                        <th>Cantidad vendida</th>
                    <?php endif; ?>
                    <?php if ($vistaDetalles === 'detallesProducto'): ?>
                        <th>Descripcion</th>
                    <?php endif; ?>
                    <?php if ($productosCategorias === 'categoriaPrecio'): ?>
                        <th>Categoria precio</th>
                    <?php endif; ?>
                </tr>
                <?php foreach ($productosPaginados as $producto): ?>
                    <tr>
                        <td><?= htmlspecialchars($producto['nombre_producto']) ?></td>
                        <td><?= htmlspecialchars($producto['precio']) ?></td>
                        <td><?= htmlspecialchars($producto['cantidad_inventario']) ?></td>
                        <td><?= htmlspecialchars($producto['categoria']) ?></td>
                        <?php if ($vistaDetalles === 'detallesProducto'): ?>
                            <td><?= htmlspecialchars($producto['descripcion']) ?></td>
                        <?php endif; ?>

                        <?php if ($productosVendidos === 'cantidadVendida' or $informacionVentas === "todasVentas"): ?>
                            <td><?= htmlspecialchars($producto['cantidad']) ?></td>
                        <?php endif; ?>

                        <?php if ($productosCategorias === 'categoriaPrecio'): ?>
                            <td><?= htmlspecialchars($producto['categoria_precio'] ?? '') ?></td>
                        <?php endif; ?>

                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No se encontraron productos.</p>
        <?php endif; ?>

    </div>
    <div class="paginacion">
    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
        <a class="numero-pagina <?= $i == $paginaActual ? 'activo' : '' ?>"
            href="?<?= $queryString ?>&pagina=<?= $i ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>

</div>
    <h2 class="titulo">Informacion pedido</h2>

    <div class="contenedor-lista">
        <?php if (!empty($pedidos)): ?>
            <table border="1">
                <tr>
                    <th>ID Pedido</th>
                    <th>Total</th>
                </tr>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td><?= htmlspecialchars($pedido['id_pedido']) ?></td>

                        <td><?= htmlspecialchars($pedido['total_pedido']) ?></td>




                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No se encontraron pedidos</p>
        <?php endif; ?>
    </div>
</body>

</html>
