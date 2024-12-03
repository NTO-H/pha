<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos Capturados</title>
</head>
<body>
    <h1>Datos Capturados</h1>
    <p>Los datos capturados se mostrarán abajo y se guardarán en el archivo visitors.log.</p>

    <h2>Información Capturada:</h2>
    <div style="border: 1px solid #ccc; padding: 10px; margin-top: 10px;">
        <?php
        // Función principal para capturar y escribir los datos
        function write_visita() {
            $archivo = "visitors.log"; // Ruta del archivo log
            $ip_excluir = "mi.ip.";    // IP a excluir (opcional)
            $new_ip = get_client_ip(); // Obtener IP del cliente

            // Verificar si la IP no está excluida
            if ($new_ip !== $ip_excluir) {
                $now = new DateTime(); // Obtener la fecha y hora actual

                // URL accedida
                $peticion = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'Sin datos';
                $datos = "Petición: " . $peticion;

                // Obtener ubicación de la IP
                $ubicacion = ip_info($new_ip, "address");

                // Crear el texto para el log
                $txt = str_pad($new_ip, 25) . " " .
                    str_pad($now->format('Y-m-d H:i:s'), 25) . " " .
                    str_pad($ubicacion, 50) . " " . $datos;

                // Guardar en el archivo log
                file_put_contents($archivo, $txt . PHP_EOL, FILE_APPEND);

                // Mostrar los datos en la página
                echo "<p><strong>IP capturada:</strong> $new_ip</p>";
                echo "<p><strong>Ubicación:</strong> $ubicacion</p>";
                echo "<p><strong>Fecha:</strong> " . $now->format('Y-m-d H:i:s') . "</p>";
            } else {
                echo "<p>La IP ha sido excluida de la captura.</p>";
            }
        }

        // Función para obtener la IP del cliente
        function get_client_ip() {
            $ipaddress = '';
            if (getenv('HTTP_CLIENT_IP')) $ipaddress = getenv('HTTP_CLIENT_IP');
            else if (getenv('HTTP_X_FORWARDED_FOR')) $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
            else if (getenv('HTTP_X_FORWARDED')) $ipaddress = getenv('HTTP_X_FORWARDED');
            else if (getenv('HTTP_FORWARDED_FOR')) $ipaddress = getenv('HTTP_FORWARDED_FOR');
            else if (getenv('HTTP_FORWARDED')) $ipaddress = getenv('HTTP_FORWARDED');
            else if (getenv('REMOTE_ADDR')) $ipaddress = getenv('REMOTE_ADDR');
            else $ipaddress = 'UNKNOWN';
            return $ipaddress;
        }

        // Función para obtener información de la IP (ubicación, ciudad, país, etc.)
        function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
            $output = NULL;
            if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
                $ip = $_SERVER["REMOTE_ADDR"];
                if ($deep_detect) {
                    if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                        $ip = $_SERVER['HTTP_CLIENT_IP'];
                }
            }
            $purpose = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
            $support = array("country", "countrycode", "state", "region", "city", "location", "address");

            // Obtener la información geográfica de la IP
            if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
                $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
                if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
                    switch ($purpose) {
                        case "address":
                            $address = array($ipdat->geoplugin_countryName);
                            if (@strlen($ipdat->geoplugin_regionName) >= 1)
                                $address[] = $ipdat->geoplugin_regionName;
                            if (@strlen($ipdat->geoplugin_city) >= 1)
                                $address[] = $ipdat->geoplugin_city;
                            $output = implode(", ", array_reverse($address));
                            break;
                    }
                }
            }
            return $output;
        }

        // Llamar a la función para capturar y mostrar los datos
        write_visita();
        ?>
    </div>
</body>
</html>
