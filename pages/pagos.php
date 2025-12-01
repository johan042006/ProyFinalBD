<?php
    // Simulamos que esto viene de la base de datos
    // Puedes cambiar este valor para probar
    $saldo_actual = 24500; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos | MoviApp</title>
    
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/pagos.css">
    
    <link rel="stylesheet" href="../styles/login.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <section class="seccion-pagos">
            
            <h2 class="titulo-pagos">Métodos de Pago</h2>

            <div class="grid-metodos-pago">
                
                <article class="tarjeta-metodo">
                    <div class="cabecera-metodo">
                        <img src="https://api.iconify.design/lucide-credit-card.svg?color=%230f172a" class="icono-metodo">
                        <h3 class="titulo-metodo">Tarjeta Bancaria</h3>
                    </div>
                    
                    <p class="desc-metodo">Agrega una tarjeta para pagos automáticos al finalizar el viaje.</p>
                    
                    <form class="form-tarjeta">
                        <div class="grupo-input">
                            <label class="etiqueta">Número de tarjeta</label>
                            <input type="text" class="campo-entrada" placeholder="0000 0000 0000 0000">
                        </div>
                        
                        <div class="grupo-input">
                            <label class="etiqueta">Nombre titular</label>
                            <input type="text" class="campo-entrada" placeholder="Como aparece en la tarjeta">
                        </div>

                        <div class="fila-flexible">
                            <div class="columna-pequena">
                                <label class="etiqueta">Expira</label>
                                <input type="text" class="campo-entrada" placeholder="MM/AA">
                            </div>
                            <div class="columna-pequena">
                                <label class="etiqueta">CVV</label>
                                <input type="text" class="campo-entrada" placeholder="123">
                            </div>
                        </div>

                        <button type="button" class="boton-primario" style="margin-top: 0.5rem;">Guardar Tarjeta</button>
                    </form>
                </article>

                <article class="tarjeta-metodo">
                    <div class="cabecera-metodo">
                        <img src="https://api.iconify.design/lucide-banknote.svg?color=%230f172a" class="icono-metodo">
                        <h3 class="titulo-metodo">Pago en Efectivo</h3>
                    </div>
                    
                    <p class="desc-metodo">Paga directamente al conductor al finalizar el viaje. Debes tener el cambio exacto preferiblemente.</p>
                    
                    <label class="contenedor-toggle">
                        <input type="checkbox" class="input-toggle" checked> <div class="visual-toggle"></div>
                        <span class="texto-toggle">Habilitar efectivo</span>
                    </label>
                </article>

                <article class="tarjeta-metodo">
                    <div class="cabecera-metodo">
                        <img src="https://api.iconify.design/lucide-wallet.svg?color=%230f172a" class="icono-metodo">
                        <h3 class="titulo-metodo">Billetera Digital</h3>
                    </div>
                    
                    <p class="desc-metodo">Recarga saldo para pagar sin contacto y sin tarjetas.</p>
                    
                    <div class="saldo-billetera">
                        <span class="etiqueta-saldo">Saldo disponible:</span>
                        <span class="valor-saldo">$<?php echo number_format($saldo_actual, 0, ',', '.'); ?></span>
                    </div>

                    <div class="acciones-billetera">
                        <button class="btn-billetera">Recargar</button>
                        <button class="btn-billetera">Historial</button>
                    </div>
                </article>

            </div>

        </section>
    </main>

</body>
</html>