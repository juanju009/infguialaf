<?php
/* =====================================================================
   GUÍA EDUCATIVA INDEPENDIENTE — BUEN USO DE SERVICIOS LAFISE NICARAGUA
   Archivo: index.php  (PHP 7.4+ / 8.x, sin dependencias)
   ---------------------------------------------------------------------
   ESTE SITIO NO ES BANCO LAFISE NI NINGÚN BANCO.
   No recibe pagos, transferencias ni datos bancarios.
   ---------------------------------------------------------------------
   CÓMO PERSONALIZAR: edite SOLO el bloque $SITE de abajo.
   Los campos marcados como 'PENDIENTE' deben completarse antes de publicar.
   ===================================================================== */

declare(strict_types=1);
session_start();

/* ============================ SITE_CONFIG ============================ */
$SITE = [
    // --- Identidad de ESTA guía (no del banco) -------------------------
    'nombre'        => 'Guía Bancaria Responsable',            // ✏️ Nombre de su guía
    'subtitulo'     => 'Orientación educativa independiente',  // ✏️
    'responsable'   => 'ALDRUBAL MORALES MARIN',                // ✏️ OBLIGATORIO: responsable del sitio

    // --- Patrocinador / anunciante (franja "Patrocinado por") ----------
    'patrocinador'          => 'ALDRUBAL MORALES MARIN',  // ✏️ Vacío = ocultar franja
    'patrocinador_doc_tipo' => 'Cédula',                  // ✏️
    'patrocinador_doc'      => '1.040.734.149',           // ✏️
    'email'         => '',          // ✏️ Correo de contacto de la guía (ej. contacto@sudominio.com). Vacío = formulario oculto
    'url_canonica'  => '',          // ✏️ URL real del sitio (ej. https://sudominio.com/). Vacío = sin canonical
    'anio'          => date('Y'),

    // --- Entidad sobre la que se orienta (enlaces oficiales verificados) ---
    'entidad'       => 'Banco LAFISE Nicaragua',
    'url_oficial'   => 'https://www.lafise.com/blb/',           // Sitio oficial verificado
    'app_oficial'   => 'LAFISE Bancanet',                       // Nombre de la app oficial
    'url_regulador' => 'https://www.siboif.gob.ni/',            // Superintendencia de Bancos (SIBOIF)

    // --- SEO ------------------------------------------------------------
    'seo_titulo'      => 'Guía educativa para usar los servicios de LAFISE Nicaragua de forma segura',
    'seo_descripcion' => 'Guía informativa independiente: consejos para usar banca en línea, app, tarjetas y cajeros de LAFISE en Nicaragua con seguridad. No somos un banco ni recibimos pagos.',

    // --- Medición (Google Ads / GTM) -----------------------------------
    // Deje vacío si no usa medición. Solo se carga tras aceptar cookies.
    'gtm_id'        => '',          // ✏️ ej. GTM-XXXXXXX (no invente IDs)
    'google_ads_id' => '',          // ✏️ ej. AW-123456789 (de su cuenta de Google Ads)
    'banner_cookies'=> true,        // Barra de preferencias de privacidad

    // --- Avisos ---------------------------------------------------------
    'modal_inicial' => true,        // Aviso "No somos un banco" al entrar (se muestra una vez)
];
/* ========================== FIN SITE_CONFIG ========================== */

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

/* ----------------- Formulario de contacto (opcional) ----------------- */
$formActivo = filter_var($SITE['email'], FILTER_VALIDATE_EMAIL) !== false;
$formEstado = $_GET['estado'] ?? '';
$formErrores = [];

if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }

if ($formActivo && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['form_contacto'])) {
    $nombre  = trim((string)($_POST['nombre']  ?? ''));
    $correo  = trim((string)($_POST['correo']  ?? ''));
    $mensaje = trim((string)($_POST['mensaje'] ?? ''));
    $trampa  = (string)($_POST['sitio_web'] ?? '');           // honeypot anti-spam
    $token   = (string)($_POST['csrf'] ?? '');

    if (!hash_equals($_SESSION['csrf'], $token)) { $formErrores[] = 'La sesión expiró. Recargue la página e inténtelo de nuevo.'; }
    if ($trampa !== '') { $formErrores[] = 'Envío no válido.'; }
    if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 80) { $formErrores[] = 'Escriba su nombre.'; }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) { $formErrores[] = 'Escriba un correo válido.'; }
    if (mb_strlen($mensaje) < 10 || mb_strlen($mensaje) > 2000) { $formErrores[] = 'El mensaje debe tener entre 10 y 2000 caracteres.'; }
    if (!empty($_SESSION['ultimo_envio']) && time() - (int)$_SESSION['ultimo_envio'] < 60) { $formErrores[] = 'Espere un minuto antes de enviar otro mensaje.'; }
    // Bloqueo preventivo: números largos que parezcan tarjetas o cuentas
    if (preg_match('/\d[\d\s-]{11,}\d/', $mensaje)) { $formErrores[] = 'Por su seguridad, no incluya números de tarjeta, cuenta ni claves.'; }

    if (!$formErrores) {
        $limpio = static function(string $v): string { return str_replace(["\r", "\n"], ' ', $v); };
        $asunto = '=?UTF-8?B?' . base64_encode('Consulta desde ' . $SITE['nombre']) . '?=';
        $cuerpo = "Nombre: {$nombre}\nCorreo: {$correo}\n\nMensaje:\n{$mensaje}\n";
        $cabeceras = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n"
                   . 'Reply-To: ' . $limpio($correo) . "\r\n";
        $ok = @mail($SITE['email'], $asunto, $cuerpo, $cabeceras);
        $_SESSION['ultimo_envio'] = time();
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
        header('Location: ?estado=' . ($ok ? 'enviado' : 'error') . '#contacto', true, 303);
        exit;
    }
}

/* ------------------------- Contenido editable ------------------------ */
$NAV = [
    ['#aviso', 'Quiénes somos'], ['#canales', 'Canales oficiales'], ['#buenas-practicas', 'Buenas prácticas'],
    ['#fraudes', 'Evitar fraudes'], ['#que-hacer', 'Qué hacer si…'], ['#faq', 'Preguntas'],
];

$PRACTICAS = [
    ['ico' => 'lock',  't' => 'Banca en línea', 'd' => 'Escriba usted mismo la dirección del sitio oficial o use un marcador guardado. No entre desde enlaces recibidos por SMS, correo o redes sociales.'],
    ['ico' => 'phone', 't' => 'App móvil', 'd' => 'Descargue la app oficial (' . $SITE['app_oficial'] . ') solo desde Google Play o App Store, verifique el desarrollador y mantenga el teléfono actualizado y con bloqueo de pantalla.'],
    ['ico' => 'card',  't' => 'Tarjetas', 'd' => 'Firme su tarjeta, no preste su PIN, active las notificaciones de consumo si su banco las ofrece y revise sus movimientos con frecuencia.'],
    ['ico' => 'atm',   't' => 'Cajeros automáticos', 'd' => 'Cubra el teclado al digitar, observe si hay piezas sueltas o extrañas en la ranura y no acepte ayuda de desconocidos.'],
    ['ico' => 'send',  't' => 'Transferencias', 'd' => 'Confirme el nombre del titular y el número de cuenta antes de enviar. Desconfíe de urgencias, premios o “errores” que le pidan devolver dinero.'],
    ['ico' => 'key',   't' => 'Contraseñas y códigos', 'd' => 'Use claves únicas y largas. Nunca comparta códigos de verificación (OTP/token): sirven para autorizar operaciones a su nombre.'],
];

$FRAUDES = [
    ['t' => 'Phishing (correo o SMS falso)', 'd' => 'Mensajes que imitan a un banco con enlaces a páginas copiadas para robar usuario y clave.'],
    ['t' => 'Vishing (llamada falsa)', 'd' => 'Alguien dice ser “del banco” y pide claves, códigos o que instale una aplicación de control remoto.'],
    ['t' => 'Perfiles falsos en redes', 'd' => 'Cuentas que copian logos y nombres para ofrecer préstamos “rápidos” a cambio de un pago previo.'],
    ['t' => 'Falsos intermediarios', 'd' => 'Personas o sitios que cobran por “agilizar” trámites. Los trámites se hacen directamente con la entidad.'],
];

$TABLA = [
    ['Recibí un SMS o correo con un enlace “del banco”', 'No abra el enlace. Ingrese escribiendo la dirección oficial.', 'Canales oficiales del banco'],
    ['Me llamaron pidiendo un código o clave', 'Cuelgue. No comparta nada. Llame usted al número publicado en el sitio oficial.', 'Atención al cliente oficial'],
    ['Veo un cargo que no reconozco', 'Bloquee la tarjeta desde la app o por teléfono y reporte el movimiento.', 'Banco (reclamo formal)'],
    ['Perdí mi tarjeta o me la robaron', 'Bloquéela de inmediato y solicite reposición.', 'Banco'],
    ['Mi reclamo no fue resuelto', 'Conserve su número de reclamo y documentos y consulte las opciones del regulador.', 'SIBOIF (Superintendencia)'],
];

$FAQ = [
    ['¿Ustedes son Banco LAFISE?', 'No. Somos una guía educativa independiente. No somos Banco LAFISE, no pertenecemos a ningún grupo financiero y no somos un banco.'],
    ['¿Puedo hacer pagos o transferencias a través de esta página?', 'No. No recibimos ni efectuamos pagos, depósitos ni transferencias de ningún tipo. Si alguien le pide dinero en nombre de esta guía, es un intento de fraude.'],
    ['¿Pueden revisar mi cuenta o mi saldo?', 'No. No tenemos acceso a cuentas ni a sistemas bancarios. Para consultar su información, use los canales oficiales del banco.'],
    ['¿Pueden tramitar un préstamo o una tarjeta por mí?', 'No. Solo orientamos sobre buenas prácticas. Las solicitudes se hacen directamente con la entidad financiera en sus canales oficiales.'],
    ['¿Por qué me piden no enviar datos bancarios?', 'Porque nunca los necesitamos. No comparta números de tarjeta, cuentas, claves, PIN ni códigos con esta guía ni con nadie.'],
    ['¿La información de esta guía es oficial?', 'No. Es contenido educativo general. Las condiciones, tarifas y requisitos vigentes solo los informa el banco en sus canales oficiales.'],
];

/* Iconos SVG (trazos simples, sin recursos externos) */
function ico(string $n): string {
    $p = [
        'lock'   => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        'phone'  => '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>',
        'card'   => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/>',
        'atm'    => '<rect x="3" y="3" width="18" height="12" rx="2"/><path d="M7 19h10M9 15v4M15 15v4M8 9h8"/>',
        'send'   => '<path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7z"/>',
        'key'    => '<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M17 6l3 3"/>',
        'shield' => '<path d="M12 2 4 5v6c0 5 3.5 9.5 8 11 4.5-1.5 8-6 8-11V5l-8-3z"/><path d="m9 12 2 2 4-4"/>',
        'x'      => '<circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/>',
        'check'  => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'alert'  => '<path d="M12 3 2 20h20L12 3z"/><path d="M12 10v4M12 17h.01"/>',
        'ext'    => '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
    ];
    return '<svg class="ico" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . ($p[$n] ?? '') . '</svg>';
}
?><!DOCTYPE html>
<html lang="es-NI">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($SITE['seo_titulo']) ?></title>
<meta name="description" content="<?= h($SITE['seo_descripcion']) ?>">
<meta name="robots" content="index, follow">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= h($SITE['seo_titulo']) ?>">
<meta property="og:description" content="<?= h($SITE['seo_descripcion']) ?>">
<?php if ($SITE['url_canonica'] !== ''): ?>
<link rel="canonical" href="<?= h($SITE['url_canonica']) ?>">
<meta property="og:url" content="<?= h($SITE['url_canonica']) ?>">
<?php endif; ?>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%231e3a5f'/><path d='M16 6 8 9v6c0 5 3.5 9 8 11 4.5-2 8-6 8-11V9z' fill='%23f2b134'/></svg>">

<style>
/* ===================== 1. VARIABLES (personalizables) ===================== */
:root{
  --c-primario:#1e3a5f;      /* azul tinta: identidad propia de la guía */
  --c-primario-osc:#142842;
  --c-acento:#f2b134;        /* ámbar */
  --c-alerta:#b42318;
  --c-ok:#1f7a4d;
  --c-fondo:#f7f5f0;
  --c-fondo-alt:#ffffff;
  --c-suave:#eef2f7;
  --c-texto:#1c2430;
  --c-texto-2:#4a5566;
  --c-borde:#dde3ea;
  --f-base:"Segoe UI",system-ui,-apple-system,Roboto,"Helvetica Neue",Arial,sans-serif;
  --f-titulo:Georgia,"Times New Roman",serif;
  --ancho:1140px;
  --esp:clamp(3.5rem,7vw,6rem);
  --r:14px; --r-sm:8px;
  --sombra:0 1px 2px rgba(20,40,66,.06),0 8px 24px rgba(20,40,66,.07);
  --t:.2s ease;
}

/* ===================== 2. BASE ===================== */
*,*::before,*::after{box-sizing:border-box}
html{scroll-behavior:smooth;scroll-padding-top:90px}
body{margin:0;font-family:var(--f-base);color:var(--c-texto);background:var(--c-fondo);line-height:1.65;font-size:1.0625rem;overflow-x:hidden}
h1,h2,h3{font-family:var(--f-titulo);line-height:1.2;color:var(--c-primario-osc);margin:0 0 .6em}
h1{font-size:clamp(2rem,4.6vw,3.25rem);letter-spacing:-.01em}
h2{font-size:clamp(1.6rem,3vw,2.25rem)}
h3{font-size:1.2rem;font-family:var(--f-base);font-weight:700}
p{margin:0 0 1rem}
a{color:var(--c-primario)}
a:focus-visible,button:focus-visible,summary:focus-visible,input:focus-visible,textarea:focus-visible{outline:3px solid var(--c-acento);outline-offset:2px;border-radius:4px}
.contenedor{width:min(100% - 2.5rem,var(--ancho));margin-inline:auto}
.ico{width:1.4em;height:1.4em;flex-shrink:0}
.saltar{position:absolute;left:-999px;top:0;background:#fff;padding:.6rem 1rem;z-index:200}
.saltar:focus{left:1rem}
.seccion{padding-block:var(--esp)}
.seccion--alt{background:var(--c-fondo-alt)}
.eyebrow{display:inline-block;font-size:.8rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--c-primario);margin-bottom:.8rem}
.lead{font-size:1.15rem;color:var(--c-texto-2);max-width:62ch}
.cabecera-seccion{max-width:720px;margin-bottom:2.5rem}

/* ===================== 3. FRANJA DE AVISO SUPERIOR ===================== */
.franja{background:var(--c-primario-osc);color:#fff;font-size:.9rem;text-align:center;padding:.55rem 1rem}
.franja strong{color:var(--c-acento)}

/* ===================== 4. ENCABEZADO ===================== */
.header{position:sticky;top:0;z-index:50;background:rgba(247,245,240,.92);backdrop-filter:blur(8px);border-bottom:1px solid var(--c-borde)}
.header__in{display:flex;align-items:center;justify-content:space-between;gap:1rem;min-height:70px}
.marca{display:flex;align-items:center;gap:.7rem;text-decoration:none;color:var(--c-primario-osc)}
.marca__logo{width:40px;height:40px;border-radius:10px;background:var(--c-primario);display:grid;place-items:center;color:var(--c-acento)}
.marca__txt{line-height:1.15}
.marca__txt b{display:block;font-family:var(--f-titulo);font-size:1.1rem}
.marca__txt small{color:var(--c-texto-2);font-size:.78rem}
.nav ul{list-style:none;margin:0;padding:0;display:flex;gap:1.4rem}
.nav a{text-decoration:none;color:var(--c-texto);font-weight:600;font-size:.95rem}
.nav a:hover{color:var(--c-primario)}
.nav-btn{display:none;background:none;border:1px solid var(--c-borde);border-radius:var(--r-sm);padding:.5rem .7rem;cursor:pointer;color:var(--c-texto)}

/* ===================== 5. BOTONES ===================== */
.btn{display:inline-flex;align-items:center;gap:.5rem;padding:.85rem 1.4rem;border-radius:999px;font-weight:700;text-decoration:none;border:2px solid transparent;transition:transform var(--t),background var(--t),box-shadow var(--t);cursor:pointer;font-size:1rem;font-family:inherit}
.btn:hover{transform:translateY(-1px)}
.btn:active{transform:translateY(0)}
.btn--primario{background:var(--c-primario);color:#fff;box-shadow:0 6px 18px rgba(30,58,95,.25)}
.btn--primario:hover{background:var(--c-primario-osc)}
.btn--secundario{background:transparent;color:var(--c-primario);border-color:var(--c-primario)}
.btn--acento{background:var(--c-acento);color:var(--c-primario-osc)}
.btn .ico{width:1.1em;height:1.1em}

/* ===================== 6. HERO ===================== */
.hero{padding-block:clamp(3rem,6vw,5rem)}
.hero__grid{display:grid;grid-template-columns:1.15fr .85fr;gap:3rem;align-items:center}
.hero__chip{display:inline-flex;align-items:center;gap:.5rem;background:#fff;border:1px solid var(--c-borde);padding:.4rem .9rem;border-radius:999px;font-size:.88rem;font-weight:600;color:var(--c-alerta);margin-bottom:1.2rem}
.hero__chip .ico{width:1.1em;height:1.1em}
.hero__acciones{display:flex;flex-wrap:wrap;gap:.8rem;margin-top:1.8rem}
.hero__nota{font-size:.88rem;color:var(--c-texto-2);margin-top:1rem}
.hero__visual{position:relative}
.tarjeta-hero{background:#fff;border-radius:22px;padding:1.8rem;box-shadow:var(--sombra);border:1px solid var(--c-borde)}
.tarjeta-hero h2{font-size:1.25rem;font-family:var(--f-base)}
.lista-check{list-style:none;margin:0;padding:0;display:grid;gap:.75rem}
.lista-check li{display:flex;gap:.65rem;align-items:flex-start}
.lista-check--si .ico{color:var(--c-ok)}
.lista-check--no .ico{color:var(--c-alerta)}

/* ===================== 7. BANNER DESTACADO ===================== */
.banner{background:var(--c-primario);color:#fff}
.banner__in{display:flex;align-items:center;gap:1.2rem;padding-block:1.4rem;flex-wrap:wrap;justify-content:center;text-align:center}
.banner p{margin:0;font-weight:600}
.banner .ico{color:var(--c-acento)}

/* ===================== 8. TARJETAS / GRILLAS ===================== */
.grid{display:grid;gap:1.4rem}
.grid--2{grid-template-columns:repeat(2,1fr)}
.grid--3{grid-template-columns:repeat(3,1fr)}
.card{background:var(--c-fondo-alt);border:1px solid var(--c-borde);border-radius:var(--r);padding:1.6rem;box-shadow:var(--sombra);transition:transform var(--t),box-shadow var(--t)}
.seccion--alt .card{background:var(--c-fondo)}
.card:hover{transform:translateY(-2px)}
.card__ico{width:48px;height:48px;border-radius:12px;background:var(--c-suave);color:var(--c-primario);display:grid;place-items:center;margin-bottom:1rem}
.card p{color:var(--c-texto-2);margin:0}
.card--si{border-top:5px solid var(--c-ok)}
.card--no{border-top:5px solid var(--c-alerta)}
.card--fraude{border-left:5px solid var(--c-alerta)}

/* ===================== 9. CANALES OFICIALES ===================== */
.canales{display:grid;grid-template-columns:1fr 1fr;gap:2.5rem;align-items:start}
.pasos{counter-reset:paso;list-style:none;margin:0;padding:0;display:grid;gap:1rem}
.pasos li{counter-increment:paso;display:grid;grid-template-columns:44px 1fr;gap:1rem;background:var(--c-fondo-alt);border:1px solid var(--c-borde);border-radius:var(--r);padding:1.1rem}
.pasos li::before{content:counter(paso);width:44px;height:44px;border-radius:50%;background:var(--c-acento);color:var(--c-primario-osc);font-weight:800;display:grid;place-items:center}
.pasos b{display:block}
.pasos span{color:var(--c-texto-2);font-size:.97rem}
.url-box{background:var(--c-primario-osc);color:#fff;border-radius:var(--r);padding:1.6rem}
.url-box h3{color:#fff}
.url-box code{display:block;background:rgba(255,255,255,.1);padding:.8rem 1rem;border-radius:var(--r-sm);font-size:1.05rem;color:var(--c-acento);word-break:break-all;margin:.6rem 0 1rem}
.url-box p{color:#d5deea;font-size:.95rem}

/* ===================== 10. TABLA ===================== */
.tabla-wrap{overflow-x:auto;border-radius:var(--r);border:1px solid var(--c-borde);background:#fff}
table{width:100%;border-collapse:collapse;min-width:640px}
th,td{padding:1rem 1.1rem;text-align:left;vertical-align:top;border-bottom:1px solid var(--c-borde)}
th{background:var(--c-primario);color:#fff;font-size:.9rem;letter-spacing:.03em}
tr:last-child td{border-bottom:0}
tbody tr:nth-child(even){background:var(--c-suave)}
td:first-child{font-weight:600}

/* ===================== 11. FAQ ===================== */
.faq{max-width:820px;display:grid;gap:.8rem}
.faq details{background:var(--c-fondo-alt);border:1px solid var(--c-borde);border-radius:var(--r);overflow:hidden}
.seccion--alt .faq details{background:var(--c-fondo)}
.faq summary{cursor:pointer;list-style:none;padding:1.15rem 1.3rem;font-weight:700;display:flex;justify-content:space-between;gap:1rem;align-items:center}
.faq summary::-webkit-details-marker{display:none}
.faq summary::after{content:"+";font-size:1.5rem;line-height:1;color:var(--c-primario);transition:transform var(--t)}
.faq details[open] summary::after{transform:rotate(45deg)}
.faq details p{padding:0 1.3rem 1.2rem;margin:0;color:var(--c-texto-2)}

/* ===================== 12. CTA / CONTACTO ===================== */
.cta{background:linear-gradient(135deg,var(--c-primario) 0%,var(--c-primario-osc) 100%);color:#fff;border-radius:24px;padding:clamp(2rem,5vw,3.5rem);display:grid;grid-template-columns:1.3fr .7fr;gap:2rem;align-items:center}
.cta h2{color:#fff}
.cta p{color:#d5deea}
.cta__acciones{display:flex;flex-direction:column;gap:.8rem}
.form{display:grid;gap:1rem;max-width:640px}
.form label{font-weight:600;display:block;margin-bottom:.3rem}
.form input,.form textarea{width:100%;padding:.8rem 1rem;border:1px solid var(--c-borde);border-radius:var(--r-sm);font:inherit;background:#fff}
.form textarea{min-height:140px;resize:vertical}
.form .trampa{position:absolute;left:-9999px}
.form__aviso{font-size:.9rem;background:#fff4e0;border:1px solid #f3d29a;padding:.8rem 1rem;border-radius:var(--r-sm)}
.msg{padding:1rem 1.2rem;border-radius:var(--r-sm);margin-bottom:1rem;font-weight:600}
.msg--ok{background:#e6f4ec;color:var(--c-ok)}
.msg--error{background:#fdecea;color:var(--c-alerta)}

/* ===================== 13. AVISO LEGAL / FOOTER ===================== */
.descargo{background:#fff4e0;border:2px solid var(--c-acento);border-radius:var(--r);padding:1.6rem;display:flex;gap:1rem}
.descargo .ico{color:#9a6a00;width:2rem;height:2rem}
.descargo p:last-child{margin:0}
.footer{background:var(--c-primario-osc);color:#c9d3e0;padding-block:3.5rem 2rem;font-size:.95rem}
.footer a{color:#fff}
.footer__grid{display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:2.5rem;margin-bottom:2.5rem}
.footer h3{color:#fff;font-size:1rem}
.footer ul{list-style:none;padding:0;margin:0;display:grid;gap:.45rem}
.footer__legal{border-top:1px solid rgba(255,255,255,.15);padding-top:1.5rem;font-size:.85rem}
.pendiente{background:#fff3cd;color:#7a5600;padding:0 .3em;border-radius:3px}
.privacidad{max-width:820px}
.privacidad h3{margin-top:1.5rem}

/* ===================== 14. MODAL Y COOKIES ===================== */
.modal{position:fixed;inset:0;background:rgba(10,20,35,.6);display:none;align-items:center;justify-content:center;padding:1rem;z-index:100}
.modal.is-open{display:flex}
.modal__box{background:#fff;border-radius:20px;max-width:520px;width:100%;max-height:90vh;overflow:auto;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.modal__box h2{font-size:1.5rem;display:flex;gap:.6rem;align-items:center}
.modal__box h2 .ico{color:var(--c-alerta)}
.cookies{position:fixed;left:0;right:0;bottom:0;background:#1a2129;color:#e4e8ee;border-top:1px solid #2c3640;z-index:90;display:none;box-shadow:0 -6px 24px rgba(0,0,0,.25)}
.cookies.is-open{display:block}
.cookies__in{width:min(100% - 2.5rem,1360px);margin-inline:auto;display:flex;align-items:center;justify-content:space-between;gap:2rem;padding-block:1.4rem}
.cookies p{margin:0;font-size:1rem;line-height:1.6;max-width:960px}
.cookies p strong{color:#fff}
.cookies a{color:#e0a43e}
.cookies__acc{display:flex;gap:1rem;flex-shrink:0}
.cbtn{min-width:174px;padding:1rem 1.4rem;border-radius:8px;font:inherit;font-weight:700;letter-spacing:.1em;text-transform:uppercase;cursor:pointer;transition:background var(--t),border-color var(--t)}
.cbtn--rechazar{background:transparent;color:#fff;border:1.5px solid #5d6772}
.cbtn--rechazar:hover{border-color:#fff}
.cbtn--aceptar{background:#d9a441;color:#1a2129;border:1.5px solid #d9a441}
.cbtn--aceptar:hover{background:#e6b45a}
@media (max-width:820px){.cookies__in{flex-direction:column;align-items:stretch;gap:1rem}.cookies__acc .cbtn{flex:1;min-width:0}}
/* Franja patrocinador */
.patro{background:#fff;border-block:1px solid var(--c-borde)}
.patro__in{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:1.5rem;padding-block:1.6rem}
.patro__lbl{font-size:.85rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:#8a6510;text-align:center}
.patro__box{display:flex;gap:2.5rem;background:#e4efed;border:1px solid #cfe0dd;border-left:4px solid #d9a441;border-radius:8px;padding:.9rem 1.7rem}
.patro__box small{display:block;font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:var(--c-texto-2)}
.patro__box b{font-size:1.15rem;color:var(--c-texto)}
.patro__nota{font-style:italic;color:var(--c-texto-2);text-align:center}
@media (max-width:820px){.patro__in{grid-template-columns:1fr;text-align:center}.patro__box{flex-direction:column;gap:.6rem;justify-self:center;text-align:left}}
.link-btn{background:none;border:0;color:#fff;text-decoration:underline;cursor:pointer;font:inherit;padding:0}

/* ===================== 15. RESPONSIVE ===================== */
@media (max-width:980px){
  .hero__grid,.canales,.cta{grid-template-columns:1fr}
  .grid--3{grid-template-columns:repeat(2,1fr)}
  .footer__grid{grid-template-columns:1fr 1fr}
}
@media (max-width:820px){
  .nav-btn{display:inline-flex}
  .nav{position:absolute;top:100%;left:0;right:0;background:var(--c-fondo);border-bottom:1px solid var(--c-borde);display:none}
  .nav.is-open{display:block}
  .nav ul{flex-direction:column;gap:0;padding:.5rem 1.25rem 1rem}
  .nav a{display:block;padding:.75rem 0;border-bottom:1px solid var(--c-borde)}
  .header__cta{display:none}
}
@media (max-width:620px){
  .grid--2,.grid--3,.footer__grid{grid-template-columns:1fr}
  .hero__acciones .btn{width:100%;justify-content:center}
  .descargo{flex-direction:column}
  .marca__txt small{display:none}
}
@media (prefers-reduced-motion:reduce){
  *{transition:none!important;scroll-behavior:auto!important}
}
</style>
</head>
<body>
<a class="saltar" href="#contenido">Saltar al contenido</a>

<!-- Franja permanente de aclaración -->
<div class="franja" role="note">
  <strong>Sitio educativo independiente.</strong> No somos <?= h($SITE['entidad']) ?> ni ningún banco. No recibimos pagos ni transferencias.
</div>

<!-- ===================== ENCABEZADO ===================== -->
<header class="header">
  <div class="contenedor header__in">
    <a class="marca" href="#inicio" aria-label="<?= h($SITE['nombre']) ?>, inicio">
      <span class="marca__logo"><?= ico('shield') ?></span>
      <span class="marca__txt"><b><?= h($SITE['nombre']) ?></b><small><?= h($SITE['subtitulo']) ?></small></span>
    </a>
    <button class="nav-btn" type="button" aria-expanded="false" aria-controls="nav" data-nav-toggle>Menú</button>
    <nav class="nav" id="nav" aria-label="Secciones">
      <ul>
        <?php foreach ($NAV as [$href, $txt]): ?>
        <li><a href="<?= h($href) ?>"><?= h($txt) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
  </div>
</header>

<main id="contenido">

<!-- ===================== HERO ===================== -->
<section class="hero" id="inicio">
  <div class="contenedor hero__grid">
    <div>
      <span class="hero__chip"><?= ico('alert') ?> No somos un banco · Solo orientación educativa</span>
      <h1>Aprenda a usar los servicios de LAFISE en Nicaragua de forma segura</h1>
      <p class="lead">Una guía práctica e independiente con consejos para la banca en línea, la app, las tarjetas y los cajeros, y para reconocer intentos de fraude antes de que le afecten.</p>
      <div class="hero__acciones">
        <a class="btn btn--primario" href="#buenas-practicas" data-evento="ver_guia">Ver la guía</a>
        <a class="btn btn--secundario" href="#fraudes" data-evento="ver_fraudes">Cómo evitar fraudes</a>
      </div>
      <p class="hero__nota">Para cualquier operación, consulta de saldo o trámite, use únicamente los canales oficiales de <?= h($SITE['entidad']) ?>.</p>
    </div>
    <div class="hero__visual">
      <div class="tarjeta-hero">
        <h2>Lo que esta guía es y lo que no es</h2>
        <ul class="lista-check lista-check--si">
          <li><?= ico('check') ?> <span>Información educativa y consejos de seguridad</span></li>
          <li><?= ico('check') ?> <span>Orientación sobre cómo acudir a canales oficiales</span></li>
        </ul>
        <hr style="border:0;border-top:1px solid var(--c-borde);margin:1.2rem 0">
        <ul class="lista-check lista-check--no">
          <li><?= ico('x') ?> <span>No somos <?= h($SITE['entidad']) ?></span></li>
          <li><?= ico('x') ?> <span>No recibimos ni hacemos pagos o transferencias</span></li>
          <li><?= ico('x') ?> <span>No pedimos claves, PIN, tarjetas ni datos bancarios</span></li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- ===================== BANNER ===================== -->
<div class="banner" role="note">
  <div class="contenedor banner__in">
    <?= ico('shield') ?>
    <p>Regla de oro: nadie que le escriba o le llame necesita su clave, PIN o código de verificación.</p>
  </div>
</div>

<!-- ===================== PATROCINADOR ===================== -->
<?php if ($SITE['patrocinador'] !== ''): ?>
<section class="patro" aria-label="Patrocinador">
  <div class="contenedor patro__in">
    <span class="patro__lbl">Patrocinado por</span>
    <div class="patro__box">
      <div><small>Patrocinador</small><b><?= h($SITE['patrocinador']) ?></b></div>
      <?php if ($SITE['patrocinador_doc'] !== ''): ?><div><small><?= h($SITE['patrocinador_doc_tipo']) ?></small><b><?= h($SITE['patrocinador_doc']) ?></b></div><?php endif; ?>
    </div>
    <span class="patro__nota">Contenido educativo independiente</span>
  </div>
</section>
<?php endif; ?>

<!-- ===================== QUIÉNES SOMOS / AVISO ===================== -->
<section class="seccion seccion--alt" id="aviso">
  <div class="contenedor">
    <div class="cabecera-seccion">
      <span class="eyebrow">Quiénes somos</span>
      <h2>Una guía de orientación, no una entidad financiera</h2>
      <p class="lead">Este sitio es un proyecto educativo independiente. Nuestra única función es informar y orientar a los usuarios sobre el buen uso de los servicios de <?= h($SITE['entidad']) ?>.</p>
    </div>
    <div class="grid grid--2">
      <article class="card card--si">
        <div class="card__ico"><?= ico('check') ?></div>
        <h3>Lo que sí hacemos</h3>
        <ul class="lista-check lista-check--si">
          <li><?= ico('check') ?> <span>Explicamos buenas prácticas de seguridad bancaria.</span></li>
          <li><?= ico('check') ?> <span>Le ayudamos a identificar los canales oficiales.</span></li>
          <li><?= ico('check') ?> <span>Describimos fraudes comunes y cómo evitarlos.</span></li>
          <li><?= ico('check') ?> <span>Le indicamos a dónde acudir en cada situación.</span></li>
        </ul>
      </article>
      <article class="card card--no">
        <div class="card__ico" style="color:var(--c-alerta)"><?= ico('x') ?></div>
        <h3>Lo que no hacemos</h3>
        <ul class="lista-check lista-check--no">
          <li><?= ico('x') ?> <span>No somos Banco LAFISE ni ningún otro banco.</span></li>
          <li><?= ico('x') ?> <span>No recibimos transferencias, depósitos ni pagos de ningún tipo.</span></li>
          <li><?= ico('x') ?> <span>No efectuamos pagos ni gestionamos cuentas, préstamos o tarjetas.</span></li>
          <li><?= ico('x') ?> <span>No solicitamos datos bancarios, claves ni códigos.</span></li>
        </ul>
      </article>
    </div>
  </div>
</section>

<!-- ===================== CANALES OFICIALES ===================== -->
<section class="seccion" id="canales">
  <div class="contenedor canales">
    <div>
      <span class="eyebrow">Canales oficiales</span>
      <h2>Cómo asegurarse de que está en el lugar correcto</h2>
      <p class="lead">Los fraudes suelen empezar con una página o un perfil que imita al banco. Antes de escribir cualquier dato, verifique estos puntos.</p>
      <ol class="pasos">
        <li><div><b>Escriba la dirección usted mismo</b><span>No llegue al banco desde enlaces en SMS, WhatsApp, correos o anuncios.</span></div></li>
        <li><div><b>Revise el dominio completo</b><span>Lea la dirección de izquierda a derecha: copias como letras cambiadas o dominios extra son señal de alerta.</span></div></li>
        <li><div><b>Descargue apps solo de tiendas oficiales</b><span>Busque “<?= h($SITE['app_oficial']) ?>” en Google Play o App Store y revise el desarrollador antes de instalar.</span></div></li>
        <li><div><b>Use los teléfonos publicados en el sitio oficial</b><span>Si tiene dudas sobre una llamada, cuelgue y llame usted al número oficial.</span></div></li>
      </ol>
    </div>
    <aside class="url-box">
      <h3>Sitio oficial de <?= h($SITE['entidad']) ?></h3>
      <code><?= h($SITE['url_oficial']) ?></code>
      <p>Escriba esta dirección usted mismo en su navegador y compárela letra por letra. Esta guía no enlaza a ningún sitio externo: así se acostumbra a no llegar al banco desde enlaces de terceros. No tenemos relación con la entidad.</p>
      <a class="btn btn--acento" href="#buenas-practicas" data-evento="ver_guia">Ver buenas prácticas</a>
    </aside>
  </div>
</section>

<!-- ===================== BUENAS PRÁCTICAS ===================== -->
<section class="seccion seccion--alt" id="buenas-practicas">
  <div class="contenedor">
    <div class="cabecera-seccion">
      <span class="eyebrow">Buenas prácticas</span>
      <h2>Consejos para el uso diario de sus servicios bancarios</h2>
      <p class="lead">Hábitos sencillos que reducen el riesgo en cada canal que utiliza.</p>
    </div>
    <div class="grid grid--3">
      <?php foreach ($PRACTICAS as $p): ?>
      <article class="card">
        <div class="card__ico"><?= ico($p['ico']) ?></div>
        <h3><?= h($p['t']) ?></h3>
        <p><?= h($p['d']) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== FRAUDES ===================== -->
<section class="seccion" id="fraudes">
  <div class="contenedor">
    <div class="cabecera-seccion">
      <span class="eyebrow">Prevención</span>
      <h2>Fraudes comunes y cómo reconocerlos</h2>
      <p class="lead">Las señales se repiten: urgencia, presión, premios inesperados y peticiones de claves o pagos por adelantado.</p>
    </div>
    <div class="grid grid--2">
      <?php foreach ($FRAUDES as $f): ?>
      <article class="card card--fraude">
        <h3><?= h($f['t']) ?></h3>
        <p><?= h($f['d']) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== QUÉ HACER SI… (TABLA) ===================== -->
<section class="seccion seccion--alt" id="que-hacer">
  <div class="contenedor">
    <div class="cabecera-seccion">
      <span class="eyebrow">Guía rápida</span>
      <h2>Qué hacer si…</h2>
      <p class="lead">Una referencia rápida para actuar a tiempo. Ante cualquier problema con su cuenta, el canal correcto siempre es la entidad financiera.</p>
    </div>
    <div class="tabla-wrap" tabindex="0" role="region" aria-label="Tabla de situaciones y acciones recomendadas">
      <table>
        <thead><tr><th scope="col">Situación</th><th scope="col">Qué hacer</th><th scope="col">A dónde acudir</th></tr></thead>
        <tbody>
          <?php foreach ($TABLA as [$a, $b, $c]): ?>
          <tr><td><?= h($a) ?></td><td><?= h($b) ?></td><td><?= h($c) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p style="margin-top:1rem;font-size:.95rem;color:var(--c-texto-2)">Regulador bancario en Nicaragua: Superintendencia de Bancos y de Otras Instituciones Financieras (SIBOIF).</p>
  </div>
</section>

<!-- ===================== FAQ ===================== -->
<section class="seccion" id="faq">
  <div class="contenedor">
    <div class="cabecera-seccion">
      <span class="eyebrow">Preguntas frecuentes</span>
      <h2>Resolvemos sus dudas sobre esta guía</h2>
    </div>
    <div class="faq">
      <?php foreach ($FAQ as $i => [$q, $a]): ?>
      <details<?= $i === 0 ? ' open' : '' ?>>
        <summary><?= h($q) ?></summary>
        <p><?= h($a) ?></p>
      </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== CTA ===================== -->
<section class="seccion seccion--alt" id="contacto">
  <div class="contenedor">
    <div class="cta">
      <div>
        <h2>¿Necesita hacer una operación o un trámite?</h2>
        <p>Hágalo directamente en los canales oficiales del banco, ingresando usted mismo. Esta guía no puede realizar operaciones, consultar cuentas ni recibir dinero.</p>
      </div>
      <div class="cta__acciones">
        <a class="btn btn--acento" href="#canales" data-evento="ver_canales">Cómo identificar los canales oficiales</a>
        <a class="btn btn--secundario" style="color:#fff;border-color:#fff" href="#buenas-practicas">Repasar consejos</a>
      </div>
    </div>

    <?php if ($formActivo): ?>
    <div style="margin-top:3.5rem">
      <h2>Escríbale a esta guía</h2>
      <p class="lead">Sugerencias o dudas sobre el contenido educativo. No atendemos asuntos de cuentas ni operaciones.</p>
      <?php if ($formEstado === 'enviado'): ?><div class="msg msg--ok" role="status">Gracias. Su mensaje fue enviado.</div><?php endif; ?>
      <?php if ($formEstado === 'error'): ?><div class="msg msg--error" role="alert">No se pudo enviar el mensaje. Inténtelo más tarde.</div><?php endif; ?>
      <?php if ($formErrores): ?><div class="msg msg--error" role="alert"><?= implode('<br>', array_map('h', $formErrores)) ?></div><?php endif; ?>
      <form class="form" method="post" action="#contacto" novalidate data-form-contacto>
        <p class="form__aviso"><strong>No incluya</strong> números de tarjeta o cuenta, claves, PIN ni códigos. No los necesitamos.</p>
        <input type="hidden" name="form_contacto" value="1">
        <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
        <div class="trampa" aria-hidden="true"><label for="sitio_web">No completar</label><input id="sitio_web" name="sitio_web" tabindex="-1" autocomplete="off"></div>
        <div><label for="nombre">Nombre</label><input id="nombre" name="nombre" required maxlength="80" autocomplete="name" value="<?= h((string)($_POST['nombre'] ?? '')) ?>"></div>
        <div><label for="correo">Correo electrónico</label><input id="correo" name="correo" type="email" required autocomplete="email" value="<?= h((string)($_POST['correo'] ?? '')) ?>"></div>
        <div><label for="mensaje">Mensaje</label><textarea id="mensaje" name="mensaje" required maxlength="2000"><?= h((string)($_POST['mensaje'] ?? '')) ?></textarea></div>
        <div><button class="btn btn--primario" type="submit">Enviar mensaje</button></div>
      </form>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ===================== DESCARGO + PRIVACIDAD ===================== -->
<section class="seccion" id="descargo">
  <div class="contenedor">
    <div class="descargo" role="note">
      <?= ico('alert') ?>
      <div>
        <h2 style="font-size:1.4rem">Aviso importante</h2>
        <p><strong>Este sitio no es Banco LAFISE, no es un banco y no es una entidad financiera.</strong> No tenemos relación, afiliación, patrocinio ni autorización de Banco LAFISE ni del Grupo LAFISE.</p>
        <p><strong>No recibimos transferencias bancarias, depósitos ni ningún tipo de pago, y tampoco efectuamos pagos.</strong> Si alguien le solicita dinero o datos bancarios en nombre de esta guía, se trata de un fraude.</p>
        <p>Nuestra única función es informar y orientar sobre el buen uso de los servicios de <?= h($SITE['entidad']) ?>. El contenido es general y educativo; las condiciones, tarifas y requisitos vigentes solo los informa la entidad en sus canales oficiales. “LAFISE” y sus marcas pertenecen a sus respectivos titulares y se mencionan únicamente con fines informativos.</p>
      </div>
    </div>

    <div class="privacidad" id="privacidad" style="margin-top:3rem">
      <h2>Política de privacidad</h2>
      <p>Responsable del sitio: <span class="<?= strpos($SITE['responsable'], 'PENDIENTE') === 0 ? 'pendiente' : '' ?>"><?= h($SITE['responsable']) ?></span>.</p>
      <h3>Datos que recogemos</h3>
      <p>Esta guía no solicita ni almacena datos bancarios. Si usa el formulario de contacto, recibimos únicamente su nombre, correo y mensaje para responderle; no se usan para otros fines.</p>
      <h3>Cookies y medición</h3>
      <p><?php if ($SITE['banner_cookies']): ?>Con su consentimiento, usamos cookies de Google Ads para medir la efectividad de nuestros anuncios. Si las rechaza, no se carga ninguna etiqueta de medición ni publicidad. Si las rechaza, no se activan. Puede cambiar su elección en cualquier momento desde el enlace “Preferencias de cookies” al pie de página.<?php else: ?>Actualmente este sitio no utiliza cookies de medición ni publicidad. Solo guarda en su navegador si ya vio el aviso inicial.<?php endif; ?></p>
      <h3>Sus derechos</h3>
      <p>Puede solicitar acceso o eliminación de los datos que nos haya enviado escribiendo a <?= $SITE['email'] !== '' ? '<a href="mailto:' . h($SITE['email']) . '">' . h($SITE['email']) . '</a>' : '<span class="pendiente">PENDIENTE: correo de contacto</span>' ?>.</p>
    </div>
  </div>
</section>

</main>

<!-- ===================== FOOTER ===================== -->
<footer class="footer">
  <div class="contenedor">
    <div class="footer__grid">
      <div>
        <h3><?= h($SITE['nombre']) ?></h3>
        <p>Guía educativa independiente sobre el buen uso de los servicios de <?= h($SITE['entidad']) ?>. No somos un banco. No recibimos ni efectuamos pagos.</p>
      </div>
      <div>
        <h3>Secciones</h3>
        <ul>
          <?php foreach ($NAV as [$href, $txt]): ?><li><a href="<?= h($href) ?>"><?= h($txt) ?></a></li><?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h3>Legal</h3>
        <ul>
          <li><a href="#descargo">Aviso importante</a></li>
          <li><a href="#privacidad">Política de privacidad</a></li>
          <?php if ($SITE['banner_cookies']): ?><li><button class="link-btn" type="button" data-cookies-abrir>Preferencias de cookies</button></li><?php endif; ?>
          <li><a href="#canales">Canales oficiales</a></li>
        </ul>
      </div>
    </div>
    <div class="footer__legal">
      <p>© <?= h((string)$SITE['anio']) ?> <?= h($SITE['nombre']) ?> · Responsable: <?= h($SITE['responsable']) ?><?= $SITE['patrocinador_doc'] !== '' ? ' · ' . h($SITE['patrocinador_doc_tipo']) . ' ' . h($SITE['patrocinador_doc']) : '' ?>.</p>
      <p>Este sitio no es Banco LAFISE ni está afiliado, patrocinado o autorizado por el Grupo LAFISE. No recibimos transferencias, depósitos ni pagos de ningún tipo.</p>
    </div>
  </div>
</footer>

<!-- ===================== MODAL INICIAL ===================== -->
<?php if ($SITE['modal_inicial']): ?>
<div class="modal" id="modal-aviso" role="dialog" aria-modal="true" aria-labelledby="modal-titulo">
  <div class="modal__box">
    <h2 id="modal-titulo"><?= ico('alert') ?> Antes de continuar</h2>
    <p><strong>Este sitio NO es Banco LAFISE ni ningún banco.</strong></p>
    <p>Somos una guía educativa independiente. <strong>No recibimos transferencias ni pagos</strong>, no efectuamos pagos y no pedimos datos bancarios.</p>
    <p>Para operaciones o trámites, acuda directamente a los canales oficiales del banco, escribiendo usted mismo su dirección en el navegador.</p>
    <button class="btn btn--primario" type="button" data-modal-cerrar>Entendido, ver la guía</button>
  </div>
</div>
<?php endif; ?>

<!-- ===================== BANNER COOKIES (solo si hay GTM) ===================== -->
<?php if ($SITE['banner_cookies']): ?>
<div class="cookies" id="cookies" role="region" aria-label="Preferencias de privacidad">
  <div class="cookies__in">
    <p><strong>Preferencias de privacidad.</strong> Si aceptas, usamos cookies de Google Ads para medir la efectividad de nuestros anuncios. Si rechazas, no se carga ninguna etiqueta de medición ni publicidad. <a href="#privacidad">Más información</a>.</p>
    <div class="cookies__acc">
      <button class="cbtn cbtn--rechazar" type="button" data-cookies="rechazar">Rechazar</button>
      <button class="cbtn cbtn--aceptar" type="button" data-cookies="aceptar">Aceptar</button>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
/* Configuración expuesta a JS (solo datos no sensibles) */
const SITE_CONFIG = <?= json_encode(['gtmId' => $SITE['gtm_id'], 'adsId' => $SITE['google_ads_id'], 'banner' => $SITE['banner_cookies'], 'modal' => $SITE['modal_inicial']], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>;

(function () {
  'use strict';
  const store = {
    get(k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
    set(k, v) { try { localStorage.setItem(k, v); } catch (e) {} }
  };

  /* --- Menú móvil --- */
  const btnNav = document.querySelector('[data-nav-toggle]');
  const nav = document.getElementById('nav');
  if (btnNav && nav) {
    btnNav.addEventListener('click', () => {
      const abierto = nav.classList.toggle('is-open');
      btnNav.setAttribute('aria-expanded', String(abierto));
    });
    nav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
      nav.classList.remove('is-open'); btnNav.setAttribute('aria-expanded', 'false');
    }));
  }

  /* --- Modal inicial (una vez por navegador) --- */
  const modal = document.getElementById('modal-aviso');
  if (modal && SITE_CONFIG.modal && store.get('aviso_visto') !== '1') {
    modal.classList.add('is-open');
    const cerrar = () => { modal.classList.remove('is-open'); store.set('aviso_visto', '1'); };
    modal.querySelector('[data-modal-cerrar]').focus();
    modal.querySelector('[data-modal-cerrar]').addEventListener('click', cerrar);
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('is-open')) cerrar(); });
  }

  /* --- Consentimiento + Google Ads / GTM ---
     Nada de Google se carga hasta que el visitante pulsa "Aceptar". */
  let gtmCargado = false;
  window.dataLayer = window.dataLayer || [];
  function gtag(){ window.dataLayer.push(arguments); }
  function cargarScript(src) { const s = document.createElement('script'); s.async = true; s.src = src; document.head.appendChild(s); }
  function cargarMedicion() {
    if (gtmCargado) return;
    if (!SITE_CONFIG.gtmId && !SITE_CONFIG.adsId) return;   // sin IDs no se carga nada
    gtmCargado = true;
    const todo = { ad_storage: 'granted', analytics_storage: 'granted', ad_user_data: 'granted', ad_personalization: 'granted' };
    gtag('consent', 'update', todo);
    if (SITE_CONFIG.adsId) {
      gtag('js', new Date());
      gtag('config', SITE_CONFIG.adsId);
      cargarScript('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(SITE_CONFIG.adsId));
    }
    if (SITE_CONFIG.gtmId) {
      window.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' });
      cargarScript('https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(SITE_CONFIG.gtmId));
    }
  }
  gtag('consent', 'default', { ad_storage: 'denied', analytics_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied' });
  const banner = document.getElementById('cookies');
  if (banner) {
    const estado = store.get('cookies_consent');
    if (estado === 'aceptar') cargarMedicion();
    else if (!estado) banner.classList.add('is-open');
    banner.querySelectorAll('[data-cookies]').forEach(b => b.addEventListener('click', () => {
      const eleccion = b.dataset.cookies;
      store.set('cookies_consent', eleccion);
      if (eleccion === 'aceptar') cargarMedicion();
      else if (gtmCargado) location.reload();   // retira etiquetas ya cargadas
      banner.classList.remove('is-open');
    }));
    const abrir = document.querySelector('[data-cookies-abrir]');
    if (abrir) abrir.addEventListener('click', () => banner.classList.add('is-open'));
  }

  /* --- Eventos de clic (solo con consentimiento y medición cargada) --- */
  document.querySelectorAll('[data-evento]').forEach(el => el.addEventListener('click', () => {
    if (!gtmCargado) return;
    if (SITE_CONFIG.adsId) gtag('event', el.dataset.evento);
    else window.dataLayer.push({ event: el.dataset.evento });
  }));

  /* --- Validación básica del formulario --- */
  const form = document.querySelector('[data-form-contacto]');
  if (form) form.addEventListener('submit', e => {
    if (!form.checkValidity()) { e.preventDefault(); form.reportValidity(); }
  });
})();
</script>
</body>
</html>
