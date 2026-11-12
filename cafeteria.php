 <?php
// ==========================================
// 1. CONEXION Y CREACION DE BASE DE DATOS
// ==========================================

$conexion = new mysqli("localhost", "root", "");

if ($conexion->connect_error) {
    die("Error de conexion: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");

// Crear base de datos
$sqlBD = "CREATE DATABASE IF NOT EXISTS cafeteria";

if (!$conexion->query($sqlBD)) {
    die("Error al crear la base de datos: " . $conexion->error);
}

// Seleccionar base de datos
$conexion->select_db("cafeteria");

// Crear tabla de clientes
$sqlTabla = "CREATE TABLE IF NOT EXISTS cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL
)";

if (!$conexion->query($sqlTabla)) {
    die("Error al crear la tabla: " . $conexion->error);
}

// ==========================================
// 2. PROCESAMIENTO DE FORMULARIOS
// ==========================================

$mensajeSistema = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // REGISTRAR CLIENTE
    if (isset($_POST["accion"]) && $_POST["accion"] === "guardar") {

        $nombre = trim($_POST["nombre"] ?? "");
        $correo = trim($_POST["correo"] ?? "");
        $mensaje = trim($_POST["mensaje"] ?? "");

        if ($nombre === "" || $correo === "" || $mensaje === "") {

            $mensajeSistema = "Todos los campos son obligatorios.";

        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

            $mensajeSistema = "Ingresa un correo electronico valido.";

        } else {

            $sql = "INSERT INTO cliente (nombre, correo, mensaje)
                    VALUES (?, ?, ?)";

            $stmt = $conexion->prepare($sql);

            if (!$stmt) {

                $mensajeSistema = "Error al preparar el registro.";

            } else {

                $stmt->bind_param("sss", $nombre, $correo, $mensaje);

                if ($stmt->execute()) {
                    $mensajeSistema = "Tu mensaje se registro correctamente.";
                } else {
                    $mensajeSistema = "No se pudo guardar el registro.";
                }

                $stmt->close();
            }
        }
    }

    // ELIMINAR CLIENTE
    if (isset($_POST["accion"]) && $_POST["accion"] === "eliminar") {

        $correoEliminar = trim($_POST["correoEliminar"] ?? "");

        if ($correoEliminar === "") {

            $mensajeSistema = "Debes escribir el correo que deseas eliminar.";

        } elseif (!filter_var($correoEliminar, FILTER_VALIDATE_EMAIL)) {

            $mensajeSistema = "Ingresa un correo electronico valido.";

        } else {

            $sql = "DELETE FROM cliente WHERE correo = ? LIMIT 1";

            $stmt = $conexion->prepare($sql);

            if (!$stmt) {

                $mensajeSistema = "Error al preparar la eliminacion.";

            } else {

                $stmt->bind_param("s", $correoEliminar);

                if ($stmt->execute()) {

                    if ($stmt->affected_rows > 0) {
                        $mensajeSistema = "El registro se elimino correctamente.";
                    } else {
                        $mensajeSistema = "No se encontro un registro con ese correo.";
                    }

                } else {
                    $mensajeSistema = "No se pudo eliminar el registro.";
                }

                $stmt->close();
            }
        }
    }
}

// Funcion para mostrar texto de forma segura
function limpiar($texto) {
    return htmlspecialchars($texto, ENT_QUOTES, "UTF-8");
}

// Consultar registros guardados
$resultado = $conexion->query("SELECT * FROM cliente ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cafeteria Escolar</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f8f0f7;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            background: linear-gradient(135deg, #e696db, #f3a0de);
            padding: 20px;
            text-align: center;
            color: white;
        }

        .titulo {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .titulo img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 50%;
            background: white;
            padding: 5px;
        }

        header h1 {
            margin: 0;
            font-size: 30px;
        }

        nav {
            background: #d879c7;
            padding: 15px;
            text-align: center;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-weight: bold;
        }

        nav a:hover {
            color: #ffe5fa;
        }

        section {
            padding: 30px;
            margin: 20px auto;
            max-width: 1100px;
        }

        h2 {
            text-align: center;
            color: #bd63ad;
            margin-bottom: 25px;
        }

        .productos {
            display: flex;
            justify-content: center;
            gap: 25px;
            flex-wrap: wrap;
        }

        .producto {
            background: white;
            width: 250px;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.10);
        }

        .producto h3 {
            color: #bd63ad;
        }

        .producto p {
            font-size: 17px;
        }

        button {
            background: #d879c7;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            margin: 5px;
        }

        button:hover {
            background: #bd63ad;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #e5c5df;
            padding: 12px;
            text-align: center;
        }

        th {
            background: #e696db;
            color: white;
        }

        .formulario {
            background: white;
            max-width: 550px;
            margin: auto;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.10);
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            margin: 8px 0 15px;
            border: 1px solid #d9b6d2;
            border-radius: 8px;
            font-family: Arial, sans-serif;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        label {
            font-weight: bold;
            color: #8d4c82;
        }

        .mensaje {
            background: #fce3f6;
            color: #8d4c82;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px auto;
            max-width: 550px;
        }

        .eliminar {
            margin-top: 35px;
            border-top: 2px solid #efd4e9;
            padding-top: 25px;
        }

        .registros {
            overflow-x: auto;
        }

        footer {
            background: #d879c7;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 30px;
        }

        @media (max-width: 600px) {

            header h1 {
                font-size: 23px;
            }

            nav a {
                display: inline-block;
                margin: 7px;
            }

            section {
                padding: 15px;
            }

            table {
                font-size: 13px;
            }

            th, td {
                padding: 7px;
            }
        }

    </style>

</head>

<body>

    <!-- ENCABEZADO -->

    <header>

        <div class="titulo">

            <img src="bolillo.jpg" alt="cafeteria Escolar">

            <h1>Cafeteria Escolar</h1>

        </div>

        <p>¡Disfruta de nuestros deliciosos productos!</p>

    </header>


    <!-- NAVEGACION -->

    <nav>

        <a href="#menu">Menu</a>

        <a href="#pedido">Pedido</a>

        <a href="#contacto">Contacto</a>

    </nav>


    <!-- MENSAJE DEL SISTEMA -->

    <?php if ($mensajeSistema !== ""): ?>

        <div class="mensaje">
            <?php echo limpiar($mensajeSistema); ?>
        </div>

    <?php endif; ?>


    <!-- MENU DE PRODUCTOS -->

    <section id="menu">

        <h2>Nuestro menu</h2>

        <div class="productos">

            <div class="producto">

                <h3>Hamburguesa</h3>

                <p>Deliciosa hamburguesa preparada al momento.</p>

                <p><strong>$45.00</strong></p>

                <button onclick="agregarProducto('Hamburguesa', 45)">
                    Agregar al pedido
                </button>

            </div>


            <div class="producto">

                <h3>Jugo</h3>

                <p>Refrescante jugo para acompañar tus alimentos.</p>

                <p><strong>$20.00</strong></p>

                <button onclick="agregarProducto('Jugo', 20)">
                    Agregar al pedido
                </button>

            </div>


            <div class="producto">

                <h3>Sandwich</h3>

                <p>Rico sandwich preparado con ingredientes frescos.</p>

                <p><strong>$30.00</strong></p>

                <button onclick="agregarProducto('Sandwich', 30)">
                    Agregar al pedido
                </button>

            </div>

        </div>

    </section>


    <!-- CARRITO DE COMPRAS -->

    <section id="pedido">

        <h2>Mi pedido</h2>

        <div class="registros">

            <table>

                <thead>

                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Accion</th>
                    </tr>

                </thead>

                <tbody id="tablaPedido">

                    <tr>
                        <td colspan="5">No hay productos agregados.</td>
                    </tr>

                </tbody>

            </table>

        </div>

        <h3 id="total">Total: $0.00</h3>

        <button onclick="realizarCompra()">
            Realizar compra
        </button>

    </section>


    <!-- FORMULARIO DE CONTACTO -->

    <section id="contacto">

        <h2>Contacto</h2>

        <div class="formulario">

            <form id="formulario" action="<?php echo limpiar($_SERVER['PHP_SELF']); ?>" method="POST">

                <input type="hidden" name="accion" value="guardar">

                <label for="nombre">Nombre:</label>

                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    placeholder="Escribe tu nombre"
                    maxlength="100"
                    required
                >

                <label for="correo">Correo electronico:</label>

                <input
                    type="email"
                    id="correo"
                    name="correo"
                    placeholder="ejemplo@gmail.com"
                    maxlength="100"
                    required
                >

                <label for="mensajeTexto">Mensaje:</label>

                <textarea
                    id="mensajeTexto"
                    name="mensaje"
                    placeholder="Escribe tu mensaje"
                    required
                ></textarea>

                <button type="submit">
                    Enviar mensaje
                </button>

            </form>


            <!-- FORMULARIO PARA ELIMINAR REGISTROS -->

            <div class="eliminar">

                <h2>Eliminar registro</h2>

                <form
                    action="<?php echo limpiar($_SERVER['PHP_SELF']); ?>"
                    method="POST"
                    onsubmit="return confirm('¿Estas seguro de eliminar este registro?');"
                >

                    <input type="hidden" name="accion" value="eliminar">

                    <label for="correoEliminar">
                        Correo del registro:
                    </label>

                    <input
                        type="email"
                        id="correoEliminar"
                        name="correoEliminar"
                        placeholder="Escribe el correo a eliminar"
                        required
                    >

                    <button type="submit">
                        Eliminar registro
                    </button>

                </form>

            </div>

        </div>

    </section>


    <!-- REGISTROS DE MYSQL -->

    <section>

        <h2>Registros de clientes</h2>

        <div class="registros">

            <table>

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Mensaje</th>
                    </tr>

                </thead>

                <tbody>

                    <?php if ($resultado && $resultado->num_rows > 0): ?>

                        <?php while ($fila = $resultado->fetch_assoc()): ?>

                            <tr>

                                <td><?php echo limpiar($fila["id"]); ?></td>

                                <td><?php echo limpiar($fila["nombre"]); ?></td>

                                <td><?php echo limpiar($fila["correo"]); ?></td>

                                <td><?php echo limpiar($fila["mensaje"]); ?></td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="4">No hay registros guardados.</td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>


    <!-- PIE DE PAGINA -->

    <footer>

        <p>&copy; 2026 Cafeteria Escolar. Todos los derechos reservados.</p>

    </footer>


    <!-- JAVASCRIPT -->

    <script>

        // CARRITO DE COMPRAS

        let pedido = [];


        // AGREGAR PRODUCTOS

        function agregarProducto(nombre, precio) {

            let productoExistente = pedido.find(
                producto => producto.nombre === nombre
            );

            if (productoExistente) {

                productoExistente.cantidad++;

            } else {

                pedido.push({
                    nombre: nombre,
                    precio: precio,
                    cantidad: 1
                });

            }

            mostrarPedido();

        }


        // MOSTRAR PEDIDO

        function mostrarPedido() {

            let tabla = document.getElementById("tablaPedido");

            let total = 0;

            tabla.innerHTML = "";

            if (pedido.length === 0) {

                tabla.innerHTML =
                    '<tr><td colspan="5">No hay productos agregados.</td></tr>';

            }

            pedido.forEach((producto, indice) => {

                let subtotal = producto.precio * producto.cantidad;

                total += subtotal;

                tabla.innerHTML += `
                    <tr>
                        <td>${producto.nombre}</td>
                        <td>$${producto.precio.toFixed(2)}</td>
                        <td>${producto.cantidad}</td>
                        <td>$${subtotal.toFixed(2)}</td>
                        <td>
                            <button onclick="eliminarProducto(${indice})">
                                Quitar
                            </button>
                        </td>
                    </tr>
                `;

            });

            document.getElementById("total").textContent =
                "Total: $" + total.toFixed(2);

        }


        // ELIMINAR UN PRODUCTO DEL PEDIDO

        function eliminarProducto(indice) {

            pedido.splice(indice, 1);

            mostrarPedido();

        }


        // REALIZAR COMPRA

        function realizarCompra() {

            if (pedido.length === 0) {

                alert("Primero agrega productos a tu pedido.");

                return;

            }

            let total = pedido.reduce(
                (suma, producto) =>
                    suma + producto.precio * producto.cantidad,
                0
            );

            alert(
                "¡Gracias por tu compra!\n" +
                "Total a pagar: $" + total.toFixed(2)
            );

            pedido = [];

            mostrarPedido();

        }


        // VALIDACION DEL FORMULARIO

        document.getElementById("formulario").addEventListener("submit", function(event) {

            let nombre = document.getElementById("nombre").value.trim();

            let correo = document.getElementById("correo").value.trim();

            let mensaje = document.getElementById("mensajeTexto").value.trim();

            if (nombre === "" || correo === "" || mensaje === "") {

                event.preventDefault();

                alert("Todos los campos son obligatorios.");

                return;

            }

            let expresionCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!expresionCorreo.test(correo)) {

                event.preventDefault();

                alert("Escribe un correo electronico valido.");

                return;

            }

        });

    </script>

</body>

</html>

<?php
// CERRAR CONEXION
$conexion->close();
?>