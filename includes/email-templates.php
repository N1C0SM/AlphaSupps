<?php
/**
 * ALPHASUPPS EMAIL TEMPLATES — Sistema Unificado
 * Templates modernos y coherentes para todos los emails
 */

/**
 * Template base para todos los emails
 */
function getEmailBaseTemplate($content, $title = 'AlphaSupps') {
    return "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>{$title}</title>
        <style>
            /* CSS RESET PARA EMAILS */
            body, table, td, p, a, li, blockquote {
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust: 100%;
            }
            table, td {
                mso-table-lspace: 0pt;
                mso-table-rspace: 0pt;
            }
            img {
                -ms-interpolation-mode: bicubic;
            }

            /* ESTILOS MODERNOS COHERENTES */
            body {
                margin: 0;
                padding: 0;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%);
                color: #ffffff;
                line-height: 1.6;
            }

            .email-wrapper {
                max-width: 600px;
                margin: 0 auto;
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(224, 185, 77, 0.2);
                border-radius: 24px;
                overflow: hidden;
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            }

            .email-header {
                background: linear-gradient(135deg, #e0b94d 0%, #f4c842 100%);
                padding: 40px 30px;
                text-align: center;
                position: relative;
                overflow: hidden;
            }

            .email-header::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                 opacity: 0.3;
            }

            .email-logo {
                font-size: 28px;
                font-weight: 700;
                color: #000000;
                margin-bottom: 10px;
                position: relative;
                z-index: 1;
            }

            .email-tagline {
                font-size: 16px;
                color: rgba(0, 0, 0, 0.8);
                margin: 0;
                position: relative;
                z-index: 1;
            }

            .email-content {
                padding: 40px 30px;
                background: rgba(26, 26, 26, 0.95);
                color: #ffffff;
            }

            .email-title {
                font-size: 24px;
                font-weight: 600;
                color: #e0b94d;
                margin-bottom: 20px;
                text-align: center;
            }

            .email-text {
                color: rgba(255, 255, 255, 0.9);
                font-size: 16px;
                line-height: 1.6;
                margin-bottom: 20px;
            }

            .email-highlight {
                background: rgba(224, 185, 77, 0.15);
                border-left: 4px solid #e0b94d;
                padding: 20px;
                margin: 20px 0;
                border-radius: 8px;
                color: #ffffff;
            }

            .email-card {
                background: rgba(42, 42, 42, 0.9);
                border: 1px solid rgba(224, 185, 77, 0.2);
                border-radius: 12px;
                padding: 20px;
                margin: 15px 0;
                color: #ffffff;
            }

            .email-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                margin: 20px 0;
            }

            .email-grid-item {
                background: rgba(224, 185, 77, 0.1);
                padding: 15px;
                border-radius: 8px;
                text-align: center;
                color: #ffffff;
            }

            .email-button {
                display: inline-block;
                background: linear-gradient(135deg, #e0b94d 0%, #f4c842 100%);
                color: #000000;
                padding: 16px 32px;
                border-radius: 16px;
                text-decoration: none;
                font-weight: 600;
                font-size: 16px;
                text-align: center;
                margin: 20px 0;
                box-shadow: 0 8px 25px rgba(224, 185, 77, 0.3);
                transition: all 0.3s ease;
            }

            .email-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 15px 35px rgba(224, 185, 77, 0.4);
            }

            .email-footer {
                background: rgba(15, 15, 15, 0.9);
                padding: 30px;
                text-align: center;
                border-top: 1px solid rgba(224, 185, 77, 0.2);
                color: #ffffff;
            }

            .email-footer-text {
                color: rgba(255, 255, 255, 0.6);
                font-size: 14px;
                margin-bottom: 10px;
            }

            .email-links {
                margin: 20px 0;
            }

            .email-links a {
                color: #e0b94d;
                text-decoration: none;
                margin: 0 10px;
                font-size: 14px;
            }

            .email-links a:hover {
                color: #f4c842;
                text-decoration: underline;
            }

            /* RESPONSIVE */
            @media only screen and (max-width: 600px) {
                .email-wrapper {
                    margin: 10px;
                    border-radius: 16px;
                }

                .email-header,
                .email-content,
                .email-footer {
                    padding: 20px;
                }

                .email-title {
                    font-size: 20px;
                }

                .email-button {
                    display: block;
                    width: 100%;
                    box-sizing: border-box;
                }
            }
        </style>
    </head>
    <body>
        <div class='email-wrapper'>
            <div class='email-header'>
                <div class='email-logo'>AlphaSupps</div>
                <p class='email-tagline'>Ciencia al Servicio del Rendimiento</p>
            </div>

            <div class='email-content'>
                {$content}
            </div>

            <div class='email-footer'>
                <p class='email-footer-text'>© 2024 AlphaSupps. Todos los derechos reservados.</p>
                <div class='email-links'>
                    <a href='https://alphasupps.alwaysdata.net'>Visitar Web</a> |
                    <a href='https://alphasupps.alwaysdata.net/views/contact.php'>Contacto</a> |
                    <a href='https://alphasupps.alwaysdata.net/views/login.php'>Iniciar Sesión</a>
                </div>
                <p class='email-footer-text'>
                    Recibes este email porque estás suscrito a AlphaSupps.<br>
                    <a href='[UNSUBSCRIBE_URL]' style='color: rgba(255, 255, 255, 0.6);'>Cancelar suscripción</a>
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Template para recuperación de contraseña
 */
function getPasswordResetEmail($resetLink) {
    $content = "
        <h1 class='email-title'>🔐 Restablece Tu Contraseña</h1>

        <p class='email-text'>
            Hemos recibido una solicitud para restablecer tu contraseña en AlphaSupps.
            Si no has sido tú, puedes ignorar este mensaje.
        </p>

        <div class='email-highlight'>
            <p class='email-text' style='margin: 0; color: #ffffff;'>
                <strong>Enlace seguro válido por 1 hora</strong><br>
                Haz clic en el botón para crear una nueva contraseña.
            </p>
        </div>

        <div style='text-align: center; margin: 30px 0;'>
            <a href='{$resetLink}' class='email-button'>🔑 Restablecer Contraseña</a>
        </div>

        <p class='email-text' style='font-size: 14px; color: rgba(255, 255, 255, 0.7);'>
            Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
            <span style='word-break: break-all; color: #e0b94d;'>{$resetLink}</span>
        </p>

        <p class='email-text'>
            <strong>¿Problemas con el enlace?</strong><br>
            Contacta con nuestro soporte en <a href='mailto:support@alphasupps.com' style='color: #e0b94d;'>support@alphasupps.com</a>
        </p>
    ";

    return getEmailBaseTemplate($content, 'Restablecer Contraseña - AlphaSupps');
}

/**
 * Template para confirmación de pedido
 */
function getOrderConfirmationEmail($orderData) {
    $content = "
        <h1 class='email-title'>✅ Pedido Confirmado</h1>

        <p class='email-text'>
            ¡Gracias por tu compra en AlphaSupps! Tu pedido ha sido confirmado y está siendo procesado.
        </p>

        <div class='email-highlight'>
            <h3 style='color: #e0b94d; margin-bottom: 15px;'>Detalles del Pedido</h3>
            <p><strong>Número de pedido:</strong> #{$orderData['id']}</p>
            <p><strong>Fecha:</strong> " . date('d/m/Y H:i') . "</p>
            <p><strong>Total:</strong> €{$orderData['price']}</p>
        </div>

        <div style='text-align: center; margin: 30px 0;'>
            <a href='https://alphasupps.alwaysdata.net/views/invoice.php?id={$orderData['id']}' class='email-button'>📄 Ver Factura</a>
        </div>

        <p class='email-text'>
            Recibirás actualizaciones sobre el envío de tu pedido. Si tienes alguna pregunta,
            contacta con nosotros en <a href='mailto:support@alphasupps.com' style='color: #e0b94d;'>support@alphasupps.com</a>
        </p>
    ";

    return getEmailBaseTemplate($content, 'Pedido Confirmado - AlphaSupps');
}

/**
 * Template para newsletter
 */
function getNewsletterEmail($title, $content, $unsubscribeUrl = '#') {
    $emailContent = "
        <h1 class='email-title'>{$title}</h1>
        {$content}

        <div style='text-align: center; margin: 30px 0;'>
            <a href='https://alphasupps.alwaysdata.net' class='email-button'>🏋️ Explorar Productos</a>
        </div>
    ";

    $template = str_replace('[UNSUBSCRIBE_URL]', $unsubscribeUrl, getEmailBaseTemplate($emailContent, $title));
    return $template;
}

/**
 * Template para bienvenida
 */
function getWelcomeEmail($userName) {
    $content = "
        <h1 class='email-title'>🎉 ¡Bienvenido a AlphaSupps!</h1>

        <p class='email-text'>
            ¡Hola {$userName}! Gracias por unirte a la comunidad de atletas inteligentes.
        </p>

        <div class='email-highlight'>
            <h3 style='color: #e0b94d; margin-bottom: 15px;'>¿Qué puedes hacer ahora?</h3>
            <ul style='color: #ffffff; padding-left: 20px;'>
                <li>Crear packs personalizados basados en tus objetivos</li>
                <li>Guardar carritos para compras futuras</li>
                <li>Acceder a AlphaBox (próximamente)</li>
                <li>Recibir consejos basados en evidencia científica</li>
            </ul>
        </div>

        <div style='text-align: center; margin: 30px 0;'>
            <a href='https://alphasupps.alwaysdata.net/views/custom-pack.php' class='email-button'>🚀 Crear Mi Primer Pack</a>
        </div>

        <p class='email-text'>
            Estamos aquí para ayudarte a optimizar tu rendimiento con ciencia, no con marketing.
            ¡Comienza tu transformación hoy!
        </p>
    ";

    return getEmailBaseTemplate($content, 'Bienvenido a AlphaSupps');
}

/**
 * Función mejorada para enviar emails con templates
 */
function sendStyledEmail($to, $subject, $templateFunction, ...$args) {
    $htmlBody = call_user_func($templateFunction, ...$args);
    return sendMail($to, $subject, $htmlBody);
}
?>
