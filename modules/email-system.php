<?php
/**
 * SISTEMA MODULAR DE EMAILS — AlphaSupps Unificado
 * Todos los emails con diseño coherente y moderno
 */

class EmailSystem {
    private static $brand = [
        'name' => 'AlphaSupps',
        'tagline' => 'Ciencia al Servicio del Rendimiento',
        'logo' => '🔬',
        'colors' => [
            'primary' => '#e0b94d',
            'secondary' => '#f4c842',
            'text' => '#ffffff',
            'bg' => '#0a0a0a'
        ],
        'urls' => [
            'website' => 'https://alphasupps.alwaysdata.net',
            'contact' => 'https://alphasupps.alwaysdata.net/views/contact.php',
            'login' => 'https://alphasupps.alwaysdata.net/views/login.php'
        ]
    ];

    /**
     * Template base unificado para TODOS los emails
     */
    private static function getBaseTemplate($content, $title = 'AlphaSupps') {
        $brand = self::$brand;

        return "
        <!DOCTYPE html>
        <html lang='es' xmlns='http://www.w3.org/1999/xhtml' xmlns:v='urn:schemas-microsoft-com:vml' xmlns:o='urn:schemas-microsoft-com:office:office'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <meta http-equiv='X-UA-Compatible' content='IE=edge'>
            <title>{$title} - {$brand['name']}</title>
            <style>
                /* RESET PARA EMAILS */
                body, table, td, p, a, li, blockquote {
                    -webkit-text-size-adjust: 100%;
                    -ms-text-size-adjust: 100%;
                }
                table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
                img { -ms-interpolation-mode: bicubic; border: 0; outline: none; }

                /* ESTILOS MODERNOS UNIFICADOS (SEGUROS PARA GMAIL/OUTLOOK) */
                body {
                    margin: 0;
                    padding: 0;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                    background: #f5f5f5;
                    color: #333333;
                    line-height: 1.6;
                }

                .email-wrapper {
                    max-width: 600px;
                    margin: 20px auto;
                    background: #ffffff;
                    border: 1px solid rgba(224, 185, 77, 0.3);
                    border-radius: 24px;
                    overflow: hidden;
                    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
                }

                .email-header {
                    background: linear-gradient(135deg, {$brand['colors']['primary']} 0%, {$brand['colors']['secondary']} 100%);
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
                    background: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\"><defs><pattern id=\"grain\" width=\"100\" height=\"100\" patternUnits=\"userSpaceOnUse\"><circle cx=\"25\" cy=\"25\" r=\"1\" fill=\"rgba(255,255,255,0.1)\"/><circle cx=\"75\" cy=\"75\" r=\"1\" fill=\"rgba(255,255,255,0.1)\"/></pattern></defs><rect width=\"100\" height=\"100\" fill=\"url(%23grain)\"/></svg>');
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
                    background: #ffffff;
                    color: #333333;
                }

                .email-title {
                    font-size: 24px;
                    font-weight: 600;
                    color: {$brand['colors']['primary']};
                    margin-bottom: 20px;
                    text-align: center;
                }

                .email-subtitle {
                    font-size: 18px;
                    font-weight: 500;
                    color: {$brand['colors']['secondary']};
                    margin-bottom: 15px;
                }

                .email-text {
                    color: #333333;
                    font-size: 16px;
                    line-height: 1.6;
                    margin-bottom: 20px;
                }

                .email-highlight {
                    background: #fff8e0;
                    border-left: 4px solid {$brand['colors']['primary']};
                    padding: 20px;
                    margin: 20px 0;
                    border-radius: 8px;
                    color: #444444;
                }

                .email-button {
                    display: inline-block;
                    background: linear-gradient(135deg, {$brand['colors']['primary']} 0%, {$brand['colors']['secondary']} 100%);
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

                .email-card {
                    background: #fafafa;
                    border: 1px solid rgba(224, 185, 77, 0.25);
                    border-radius: 12px;
                    padding: 20px;
                    margin: 15px 0;
                    color: #333333;
                }

                .email-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 15px;
                    margin: 20px 0;
                }

                .email-grid-item {
                    background: #fff8e0;
                    padding: 15px;
                    border-radius: 8px;
                    text-align: center;
                    color: #444444;
                }

                .email-footer {
                    background: #fafafa;
                    padding: 30px;
                    text-align: center;
                    border-top: 1px solid rgba(224, 185, 77, 0.2);
                    color: #666666;
                }

                .email-footer-text {
                    color: #777777;
                    font-size: 14px;
                    margin-bottom: 10px;
                }

                /* ENLACES GENERALES EN EMAILS */
                a {
                    text-decoration: none;
                }

                .email-links {
                    margin: 20px 0;
                }

                .email-links a {
                    color: {$brand['colors']['primary']};
                    text-decoration: none;
                    margin: 0 10px;
                    font-size: 14px;
                }

                .email-links a:hover {
                    color: {$brand['colors']['secondary']};
                    text-decoration: none;
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
                    <div class='email-logo'>{$brand['logo']} {$brand['name']}</div>
                    <p class='email-tagline'>{$brand['tagline']}</p>
                </div>

                <div class='email-content'>
                    {$content}
                </div>

                <div class='email-footer'>
                    <p class='email-footer-text'>© 2024 {$brand['name']}. Todos los derechos reservados.</p>
                    <div class='email-links'>
                        <a href='{$brand['urls']['website']}'>Visitar Web</a> |
                        <a href='{$brand['urls']['contact']}'>Contacto</a> |
                        <a href='{$brand['urls']['login']}'>Iniciar Sesión</a>
                    </div>
                    <p class='email-footer-text'>
                        Recibes este email porque estás suscrito a {$brand['name']}.<br>
                        <a href='[UNSUBSCRIBE_URL]' style='color: rgba(255, 255, 255, 0.6);'>Cancelar suscripción</a>
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * EMAIL: Recuperación de contraseña
     */
    public static function passwordReset($resetLink, $userName = null) {
        $greeting = $userName ? "Hola {$userName}," : "Hola,";

        $content = "
            <h1 class='email-title'>🔐 Restablece Tu Contraseña</h1>

            <p class='email-text'>{$greeting}</p>

            <p class='email-text'>
                Hemos recibido una solicitud para restablecer tu contraseña en AlphaSupps.
                Si no has sido tú, puedes ignorar este mensaje de forma segura.
            </p>

            <div class='email-highlight'>
                <h3 class='email-subtitle'>🔗 Enlace Seguro de Recuperación</h3>
                <p class='email-text' style='margin: 0; color: #444444; font-size: 14px;'>
                    Este enlace es válido por <strong>1 hora</strong> por seguridad.
                    Haz clic en el botón para crear una nueva contraseña.
                </p>
            </div>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$resetLink}' class='email-button'>🔑 Restablecer Contraseña</a>
            </div>

            <div class='email-card'>
                <h3 class='email-subtitle'>🛡️ Tu Seguridad es Nuestra Prioridad</h3>
                <div class='email-grid'>
                    <div class='email-grid-item'>
                        <div style='font-size: 2rem; margin-bottom: 8px;'>🔐</div>
                        <strong>Encriptación SSL</strong>
                    </div>
                    <div class='email-grid-item'>
                        <div style='font-size: 2rem; margin-bottom: 8px;'>⚡</div>
                        <strong>Recuperación Rápida</strong>
                    </div>
                    <div class='email-grid-item'>
                        <div style='font-size: 2rem; margin-bottom: 8px;'>🛡️</div>
                        <strong>100% Seguro</strong>
                    </div>
                </div>
            </div>

            <p class='email-text' style='font-size: 14px; color: rgba(255, 255, 255, 0.7);'>
                Si el botón no funciona, copia y pega esta URL en tu navegador:<br>
                <span style='word-break: break-all; color: #e0b94d;'>{$resetLink}</span>
            </p>

            <p class='email-text'>
                <strong>¿Problemas con el enlace?</strong><br>
                Contacta con nuestro soporte en <a href='mailto:support@alphasupps.com' style='color: #e0b94d;'>support@alphasupps.com</a>
            </p>
        ";

        $template = self::getBaseTemplate($content, 'Restablecer Contraseña');
        return str_replace('[UNSUBSCRIBE_URL]', '#', $template);
    }

    /**
     * EMAIL: Confirmación de pedido
     */
    public static function orderConfirmation($orderData, $userName = null) {
        $greeting = $userName ? "¡Hola {$userName}!" : "¡Hola!";

        $content = "
            <h1 class='email-title'>✅ Pedido Confirmado con Éxito</h1>

            <p class='email-text'>{$greeting}</p>

            <p class='email-text'>
                ¡Gracias por tu confianza en AlphaSupps! Tu pedido ha sido confirmado y está siendo procesado
                con nuestra metodología de calidad premium.
            </p>

            <div class='email-highlight'>
                <h3 class='email-subtitle'>📦 Detalles de Tu Pedido</h3>
                <p><strong>Número de pedido:</strong> #{$orderData['id']}</p>
                <p><strong>Fecha de confirmación:</strong> " . date('d/m/Y H:i') . "</p>
                <p><strong>Total confirmado:</strong> €" . number_format($orderData['price'] ?? 0, 2, ',', '.') . "</p>
                <p><strong>Método de envío:</strong> Envío premium 24-48h</p>
            </div>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='https://alphasupps.alwaysdata.net/views/invoice.php?id={$orderData['id']}' class='email-button'>📄 Ver Factura Detallada</a>
            </div>

            <div class='email-card'>
                <h3 class='email-subtitle'>🚚 Seguimiento de Tu Pedido</h3>
                <p class='email-text' style='margin: 0;'>
                    Recibirás actualizaciones automáticas sobre el estado de tu envío.
                    Tu pedido será preparado con nuestros estándares de calidad más altos.
                </p>
            </div>

            <p class='email-text'>
                <strong>¿Dudas sobre tu pedido?</strong><br>
                Nuestro equipo de expertos está disponible en
                <a href='mailto:support@alphasupps.com' style='color: #e0b94d;'>support@alphasupps.com</a>
            </p>
        ";

        $template = self::getBaseTemplate($content, 'Pedido Confirmado');
        return str_replace('[UNSUBSCRIBE_URL]', '#', $template);
    }

    /**
     * EMAIL: Bienvenida para nuevos usuarios
     */
    public static function welcomeUser($userName, $userEmail) {
        $content = "
            <h1 class='email-title'>🎉 ¡Bienvenido a la Comunidad AlphaSupps!</h1>

            <p class='email-text'>¡Hola {$userName}!</p>

            <p class='email-text'>
                Gracias por unirte a la comunidad de atletas que priorizan la ciencia sobre el marketing.
                Estás a punto de comenzar una transformación respaldada por evidencia real.
            </p>

            <div class='email-highlight'>
                <h3 class='email-subtitle'>🚀 ¿Qué puedes hacer ahora?</h3>
                <ul style='color: #ffffff; padding-left: 20px; margin: 15px 0;'>
                    <li><strong>Crear packs personalizados</strong> basados en tu perfil único</li>
                    <li><strong>Guardar carritos</strong> para compras futuras inteligentes</li>
                    <li><strong>Acceder a AlphaBox</strong> cuando esté disponible</li>
                    <li><strong>Recibir insights científicos</strong> sobre nutrición deportiva</li>
                </ul>
            </div>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='https://alphasupps.alwaysdata.net/views/custom-pack.php' class='email-button'>🚀 Crear Mi Primer Pack</a>
            </div>

            <div class='email-card'>
                <h3 class='email-subtitle'>🧪 Nuestra Filosofía</h3>
                <div class='email-grid'>
                    <div class='email-grid-item'>
                        <div style='font-size: 2rem; margin-bottom: 8px;'>🧪</div>
                        <strong>Ciencia Primero</strong><br>
                        <small>Todo respaldado por estudios</small>
                    </div>
                    <div class='email-grid-item'>
                        <div style='font-size: 2rem; margin-bottom: 8px;'>🎯</div>
                        <strong>Personalización</strong><br>
                        <small>Fórmulas para tu cuerpo</small>
                    </div>
                    <div class='email-grid-item'>
                        <div style='font-size: 2rem; margin-bottom: 8px;'>🌟</div>
                        <strong>Resultados Reales</strong><br>
                        <small>No promesas vacías</small>
                    </div>
                </div>
            </div>

            <p class='email-text'>
                Estamos emocionados de acompañarte en tu viaje fitness. Cada suplemento, cada decisión,
                está diseñada para maximizar tu rendimiento basado en evidencia científica.
            </p>

            <p class='email-text'>
                <strong>¿Preguntas?</strong> Estamos aquí para ayudarte.<br>
                Escríbenos a <a href='mailto:hola@alphasupps.com' style='color: #e0b94d;'>hola@alphasupps.com</a>
            </p>
        ";

        $template = self::getBaseTemplate($content, 'Bienvenido a AlphaSupps');
        return str_replace('[UNSUBSCRIBE_URL]', "https://alphasupps.alwaysdata.net/unsubscribe.php?email={$userEmail}", $template);
    }

    /**
     * EMAIL: Newsletter con contenido premium
     */
    public static function newsletter($title, $content, $featuredProduct = null, $unsubscribeUrl = '#') {
        $featuredSection = '';

        if ($featuredProduct) {
            $featuredSection = "
                <div class='email-highlight'>
                    <h3 class='email-subtitle'>⭐ Producto Destacado de la Semana</h3>
                    <p><strong>{$featuredProduct['name']}</strong></p>
                    <p>{$featuredProduct['description']}</p>
                    <div style='text-align: center; margin: 15px 0;'>
                        <a href='{$featuredProduct['url']}' class='email-button'>Ver Producto →</a>
                    </div>
                </div>
            ";
        }

        $emailContent = "
            <h1 class='email-title'>{$title}</h1>
            {$content}
            {$featuredSection}

            <div style='text-align: center; margin: 30px 0;'>
                <a href='https://alphasupps.alwaysdata.net' class='email-button'>🏋️ Explorar Catálogo</a>
            </div>

            <div class='email-card'>
                <h3 class='email-subtitle'>📚 Recursos de Interés</h3>
                <div class='email-grid'>
                    <div class='email-grid-item'>
                        <div style='font-size: 2rem; margin-bottom: 8px;'>📖</div>
                        <strong>Blog Científico</strong><br>
                        <small>Artículos respaldados</small>
                    </div>
                    <div class='email-grid-item'>
                        <div style='font-size: 2rem; margin-bottom: 8px;'>🔬</div>
                        <strong>Estudios</strong><br>
                        <small>Evidencia que funciona</small>
                    </div>
                    <div class='email-grid-item'>
                        <div style='font-size: 2rem; margin-bottom: 8px;'>💡</div>
                        <strong>Tips</strong><br>
                        <small>Optimización diaria</small>
                    </div>
                </div>
            </div>
        ";

        $template = self::getBaseTemplate($emailContent, $title);
        return str_replace('[UNSUBSCRIBE_URL]', $unsubscribeUrl, $template);
    }

    /**
     * EMAIL: Recordatorio de carrito abandonado
     */
    public static function abandonedCart($userName, $cartItems, $cartUrl, $total) {
        $itemsList = '';
        foreach (array_slice($cartItems, 0, 3) as $item) {
            $itemsList .= "<li>{$item['name']} - €{$item['price']}</li>";
        }

        if (count($cartItems) > 3) {
            $remaining = count($cartItems) - 3;
            $itemsList .= "<li>... y {$remaining} producto(s) más</li>";
        }

        $content = "
            <h1 class='email-title'>🛒 Tu Carrito Te Está Esperando</h1>

            <p class='email-text'>¡Hola {$userName}!</p>

            <p class='email-text'>
                Vimos que dejaste algunos productos excelentes en tu carrito.
                No pierdas la oportunidad de optimizar tu rendimiento con estos suplementos.
            </p>

            <div class='email-highlight'>
                <h3 class='email-subtitle'>📦 Productos en Tu Carrito</h3>
                <ul style='color: #ffffff; padding-left: 20px; margin: 15px 0;'>
                    {$itemsList}
                </ul>
                <p style='margin: 10px 0;'><strong>Total guardado: €" . number_format($total, 2, ',', '.') . "</strong></p>
            </div>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$cartUrl}' class='email-button'>🛒 Completar Compra</a>
            </div>

            <div class='email-card'>
                <h3 class='email-subtitle'>⏰ Oferta Limitada</h3>
                <p class='email-text' style='margin: 0;'>
                    Tu carrito está reservado por 24 horas. Después de este tiempo,
                    los precios podrían cambiar debido a actualizaciones científicas.
                </p>
            </div>

            <p class='email-text'>
                <strong>¿Cambiaste de idea?</strong><br>
                Ningún problema. Tu carrito queda guardado para cuando estés listo.
            </p>
        ";

        $template = self::getBaseTemplate($content, 'Carrito Pendiente');
        return str_replace('[UNSUBSCRIBE_URL]', '#', $template);
    }

    /**
     * Método unificado para enviar cualquier tipo de email
     */
    public static function send($to, $subject, $type, ...$args) {
        $emailContent = call_user_func_array([self::class, $type], $args);
        return sendMail($to, $subject, $emailContent);
    }
}

// Funciones helper para uso directo
function sendPasswordResetEmail($to, $resetLink, $userName = null) {
    return EmailSystem::send($to, '🔐 Recupera tu Contraseña - AlphaSupps', 'passwordReset', $resetLink, $userName);
}

function sendOrderConfirmationEmail($to, $orderData, $userName = null) {
    return EmailSystem::send($to, '✅ Pedido Confirmado - AlphaSupps', 'orderConfirmation', $orderData, $userName);
}

function sendWelcomeEmail($to, $userName, $userEmail) {
    return EmailSystem::send($to, '🎉 Bienvenido a AlphaSupps', 'welcomeUser', $userName, $userEmail);
}

function sendNewsletterEmail($to, $title, $content, $featuredProduct = null, $unsubscribeUrl = '#') {
    return EmailSystem::send($to, $title . ' - AlphaSupps', 'newsletter', $title, $content, $featuredProduct, $unsubscribeUrl);
}

function sendAbandonedCartEmail($to, $userName, $cartItems, $cartUrl, $total) {
    return EmailSystem::send($to, '🛒 Tu Carrito Te Espera - AlphaSupps', 'abandonedCart', $userName, $cartItems, $cartUrl, $total);
}
?>
