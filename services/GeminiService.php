<?php
/**
 * TUBI 2026 - Servicio de Chat IA Mejorado
 * Integración con Google Gemini + Base de conocimiento local
 */

class GeminiService {
    private $apiKey;
    private $apiUrl;

    // Prompts del sistema por rol - Mejorados
    private $systemPrompts = [
        'alumno' => "Sos TuBi, el asistente virtual del programa 'Tu Bicicleta' de San Luis, Argentina.
Tu rol es ayudar a ALUMNOS de escuelas secundarias que reciben bicicletas del programa.

PERSONALIDAD:
- Amigable, cercano, usá 'vos' y lenguaje informal pero respetuoso
- Motivador, celebrá sus logros y progreso
- Paciente para explicar cosas técnicas sobre bicicletas
- Usá emojis moderadamente para ser más cercano

CONOCIMIENTOS ESPECÍFICOS:
- Cuidado y mantenimiento básico de bicicletas (frenos, cadena, ruedas)
- Seguridad vial: uso obligatorio del casco, señales de tránsito, cómo circular
- El programa TuBi: cómo funciona, beneficios, responsabilidades
- Los módulos de aprendizaje y gamificación (retos matutinos/nocturnos)
- Cómo ganar puntos y desbloquear logros

INFORMACIÓN DEL PROGRAMA:
- Las bicicletas son entregadas a estudiantes de secundaria de escuelas públicas
- El programa es del Gobierno de San Luis, Secretaría de Transporte
- La bicicleta debe cuidarse y no puede venderse
- Debe usarse casco SIEMPRE, es obligatorio

INSTRUCCIONES:
- Respondé siempre en español argentino
- Si no sabés algo específico, sugerí contactar a la escuela
- Recordá la importancia del casco SIEMPRE
- Las respuestas deben ser concisas pero útiles (máx 150 palabras)",

        'tutor' => "Sos TuBi, el asistente del programa 'Tu Bicicleta' para TUTORES/PADRES de San Luis.

PERSONALIDAD:
- Profesional pero cálido y comprensivo
- Entendés las preocupaciones de seguridad de los padres
- Claro y directo en las explicaciones

CONOCIMIENTOS ESPECÍFICOS:
- Estado y proceso de entrega de bicicletas
- Documentación requerida (DNI del alumno y tutor, constancia escolar)
- Responsabilidades legales del tutor y alumno
- Seguimiento del progreso educativo en la app
- Condiciones del programa y cuidado del bien

INFORMACIÓN DEL PROGRAMA:
- El tutor es responsable legal mientras el alumno sea menor
- La bicicleta se entrega en la escuela con documentación
- Si hay robo o daño, se debe hacer denuncia policial
- El programa incluye capacitación en seguridad vial

INSTRUCCIONES:
- Usá un tono formal pero cercano
- Priorizá la tranquilidad de los padres
- Si hay consultas legales complejas, sugerí ir a la escuela",

        'proveedor' => "Sos TuBi, el asistente para PROVEEDORES del programa Tu Bicicleta.

PERSONALIDAD:
- Profesional, eficiente y orientado a resultados
- Técnico cuando se necesita
- Colaborativo con el equipo de logística

CONOCIMIENTOS ESPECÍFICOS:
- Proceso completo de armado de bicicletas (recepción, ensamble, control de calidad)
- Sistema de códigos QR y cómo escanearlo
- Suministro a escuelas y logística de entrega
- Estados de bicicletas: depósito → armada → suministrada → en_escuela
- Reportes de producción y métricas

PROCESO DE ARMADO:
1. Recibir bicicleta en caja del depósito
2. Escanear QR de recepción
3. Armar completamente (manubrio, pedales, asiento, ruedas)
4. Control de calidad (frenos, cambios, dirección)
5. Escanear QR de armado completado
6. Dejar lista para suministro

INSTRUCCIONES:
- Mantené un tono comercial profesional
- Sé específico con los procesos técnicos
- Para problemas graves, sugerí contactar al administrador",

        'escuela' => "Sos TuBi, el asistente para ESCUELAS del programa Tu Bicicleta de San Luis.

PERSONALIDAD:
- Institucional, formal y respetuoso
- Colaborativo con el personal directivo y docente
- Orientado a facilitar la gestión

CONOCIMIENTOS ESPECÍFICOS:
- Gestión de entregas a alumnos
- Registro de alumnos beneficiarios (DNI, datos, tutor)
- Asignación de bicicletas por código QR
- Generación de planillas y reportes
- Resolución de incidencias (reclamos, devoluciones)
- Coordinación con proveedores para recepción

PROCESO DE ENTREGA EN ESCUELA:
1. Recibir bicicletas del proveedor
2. Verificar códigos QR y estado
3. Coordinar con alumno y tutor para entrega
4. Verificar documentación (DNI ambos, autorización)
5. Asignar bicicleta a alumno en el sistema
6. Entregar y firmar acta de entrega

INSTRUCCIONES:
- Usá lenguaje institucional apropiado
- Sé preciso con los procedimientos administrativos
- Para casos especiales, sugerí contactar a la Secretaría",

        'admin' => "Sos TuBi Admin, el asistente para ADMINISTRADORES del Centro de Control.

PERSONALIDAD:
- Técnico, preciso y analítico
- Acceso completo a toda la información del sistema
- Proactivo en identificar problemas y oportunidades

CONOCIMIENTOS ESPECÍFICOS:
- Estadísticas completas del programa (entregas, pendientes, por zona)
- Gestión de todos los usuarios y roles
- Configuración técnica del sistema
- Análisis de datos y métricas de rendimiento
- Base de conocimiento IA y documentos cargados
- Flujo de trabajo completo del programa

MÉTRICAS CLAVE:
- Total de bicicletas en el programa
- Distribución por estado (depósito, armadas, en escuela, entregadas)
- Tasa de entrega y velocidad de procesamiento
- Escuelas activas y rendimiento por zona
- Actividad de proveedores

INSTRUCCIONES:
- Proporcioná información técnica y datos precisos
- Sugerí optimizaciones cuando sea apropiado
- Para cambios de configuración críticos, recomendá verificar"
    ];

    // Base de conocimiento TuBi
    private $knowledgeBase = [
        // FAQ General
        'que_es_tubi' => 'TuBi (Tu Bicicleta) es un programa del Gobierno de San Luis que entrega bicicletas a estudiantes de escuelas secundarias públicas para facilitar su movilidad y promover el transporte sustentable.',

        'como_obtener' => 'Para obtener tu bicicleta TuBi: 1) Tu escuela debe estar en el programa, 2) Completar la inscripción con tu DNI y el de tu tutor, 3) Completar los módulos de seguridad vial, 4) Coordinar la entrega en tu escuela.',

        'requisitos' => 'Requisitos: ser alumno regular de escuela secundaria pública de San Luis, presentar DNI del alumno y tutor, completar la capacitación de seguridad vial.',

        // Cuidado de la bici
        'cuidado_basico' => 'Cuidado básico de tu TuBi: 1) Revisá los frenos antes de cada uso, 2) Mantené las ruedas infladas (revisar semanalmente), 3) Lubricá la cadena cada mes, 4) Guardala bajo techo cuando no la uses, 5) Limpiala regularmente.',

        'frenos' => 'Los frenos deben responder inmediatamente al apretar la manija. Si están flojos o no frenan bien, no uses la bici y avisá a tu escuela para reparación.',

        'cadena' => 'La cadena debe estar limpia y lubricada. Si hace ruido, chirridos, o se sale, necesita mantenimiento. Usá aceite para cadenas de bici, nunca aceite de cocina.',

        'pinchadura' => 'Si se pincha la rueda: no sigas usando la bici, podés dañar la llanta. Llevala caminando a tu escuela o a un taller de bicicletas para reparar.',

        // Seguridad vial
        'casco' => '¡El casco es OBLIGATORIO y puede salvar tu vida! Debe estar bien ajustado (caber dos dedos entre la correa y el mentón). Nunca uses la bici sin casco.',

        'circular' => 'Circulá siempre por la derecha, respetando las señales de tránsito. Usá señales con el brazo para indicar giros. Mantené distancia de los autos.',

        'noche' => 'De noche o con poca luz: usá elementos reflectantes, luces (delantera blanca, trasera roja), y ropa clara. Sé extra precavido.',

        'señales' => 'Señales importantes: PARE (detenerse completamente), Ceda el Paso (dar prioridad), Bicisenda (carril exclusivo para bicis), Prohibido Bicicletas (no podés circular ahí).',

        // Proceso y estados
        'estados_bici' => 'Estados de una bicicleta TuBi: 1) En depósito (esperando armado), 2) Armada (lista en proveedor), 3) En escuela (esperando asignación), 4) Entregada (asignada a alumno).',

        'perdida_robo' => 'Si te roban la bici o la perdés: 1) Hacé la denuncia policial inmediatamente, 2) Avisá a tu escuela con copia de la denuncia, 3) La escuela gestionará los pasos siguientes.',

        // Gamificación
        'puntos' => 'Ganá puntos completando retos diarios, módulos de aprendizaje, manteniendo tu racha de días activo, y participando en desafíos especiales. Los puntos desbloquean logros.',

        'retos' => 'Hay retos matutinos (6-12hs), de la tarde (12-18hs) y nocturnos (18-6hs). Cada reto dura entre 3-15 minutos y te da puntos extra. ¡Completalos todos para ganar más!',

        'logros' => 'Los logros se desbloquean al cumplir objetivos: completar módulos, mantener racha de días, ganar puntos, y más. Cada logro te da una medalla en tu perfil.',
    ];

    public function __construct() {
        $this->apiKey = GEMINI_API_KEY;
        $this->apiUrl = GEMINI_API_URL;
    }

    /**
     * Enviar mensaje a Gemini y obtener respuesta
     */
    public function chat($message, $role = 'alumno', $conversationHistory = []) {
        // Primero intentar respuesta local de la base de conocimiento
        $localResponse = $this->getLocalResponse($message, $role);

        // Si hay una buena respuesta local y no hay API key, usar esa
        if ($localResponse && (empty($this->apiKey) || $this->apiKey === 'TU_API_KEY_DE_GEMINI')) {
            return [
                'success' => true,
                'content' => $localResponse,
                'source' => 'local'
            ];
        }

        // Construir el prompt con contexto del rol
        $systemPrompt = $this->systemPrompts[$role] ?? $this->systemPrompts['alumno'];

        // Agregar conocimiento base relevante al prompt
        $relevantKnowledge = $this->getRelevantKnowledge($message);
        if ($relevantKnowledge) {
            $systemPrompt .= "\n\nINFORMACIÓN RELEVANTE DE LA BASE DE CONOCIMIENTO:\n" . $relevantKnowledge;
        }

        // Construir el prompt completo
        $fullPrompt = $systemPrompt . "\n\nUsuario pregunta: " . $message;

        // Si hay historial, agregarlo
        if (!empty($conversationHistory)) {
            $historyText = "\n\nHistorial de conversación reciente:\n";
            foreach (array_slice($conversationHistory, -5) as $msg) {
                $historyText .= ($msg['role'] === 'user' ? 'Usuario: ' : 'Asistente: ') . $msg['content'] . "\n";
            }
            $fullPrompt = $systemPrompt . $historyText . "\nNueva pregunta del usuario: " . $message;
        }

        // Si no hay API key válida, usar respuesta local
        if (empty($this->apiKey) || $this->apiKey === 'TU_API_KEY_DE_GEMINI') {
            return [
                'success' => true,
                'content' => $localResponse ?: $this->getFallbackResponse($message, $role),
                'source' => 'fallback'
            ];
        }

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $fullPrompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            ],
            'safetySettings' => [
                ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE']
            ]
        ];

        // Hacer la petición a la API
        $url = $this->apiUrl . '?key=' . $this->apiKey;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => 'Error de conexión',
                'content' => $localResponse ?: $this->getFallbackResponse($message, $role)
            ];
        }

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => 'Error de API',
                'content' => $localResponse ?: $this->getFallbackResponse($message, $role)
            ];
        }

        $result = json_decode($response, true);

        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return [
                'success' => true,
                'content' => $result['candidates'][0]['content']['parts'][0]['text'],
                'source' => 'gemini'
            ];
        }

        return [
            'success' => false,
            'error' => 'Respuesta inesperada',
            'content' => $localResponse ?: $this->getFallbackResponse($message, $role)
        ];
    }

    /**
     * Buscar respuesta en base de conocimiento local
     */
    private function getLocalResponse($message, $role) {
        $message = strtolower($message);
        $message = $this->removeAccents($message);

        // Mapeo de palabras clave a respuestas
        $keywordMap = [
            // Preguntas sobre el programa
            ['keywords' => ['que es tubi', 'que es tu bici', 'programa tubi', 'que es el programa'], 'response' => 'que_es_tubi'],
            ['keywords' => ['como obtener', 'como conseguir', 'como tener', 'quiero una bici'], 'response' => 'como_obtener'],
            ['keywords' => ['requisitos', 'necesito para', 'que necesito'], 'response' => 'requisitos'],

            // Cuidado
            ['keywords' => ['cuidar', 'cuidado', 'mantener', 'mantenimiento'], 'response' => 'cuidado_basico'],
            ['keywords' => ['freno', 'frenar', 'frenos'], 'response' => 'frenos'],
            ['keywords' => ['cadena', 'aceite', 'lubricar'], 'response' => 'cadena'],
            ['keywords' => ['pinch', 'pinchazo', 'pinchadura', 'desinfl'], 'response' => 'pinchadura'],

            // Seguridad
            ['keywords' => ['casco', 'cabeza', 'proteccion'], 'response' => 'casco'],
            ['keywords' => ['circular', 'andar', 'manejar', 'conducir'], 'response' => 'circular'],
            ['keywords' => ['noche', 'oscuro', 'luz', 'reflectante'], 'response' => 'noche'],
            ['keywords' => ['senal', 'señal', 'transito', 'semaforo'], 'response' => 'señales'],

            // Estados y proceso
            ['keywords' => ['estado', 'donde esta', 'mi bici'], 'response' => 'estados_bici'],
            ['keywords' => ['robo', 'robaron', 'perdi', 'perdida'], 'response' => 'perdida_robo'],

            // Gamificación
            ['keywords' => ['punto', 'ganar', 'conseguir puntos'], 'response' => 'puntos'],
            ['keywords' => ['reto', 'desafio', 'juego', 'jugar'], 'response' => 'retos'],
            ['keywords' => ['logro', 'medalla', 'insignia', 'premio'], 'response' => 'logros'],
        ];

        foreach ($keywordMap as $map) {
            foreach ($map['keywords'] as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $baseResponse = $this->knowledgeBase[$map['response']] ?? null;
                    if ($baseResponse) {
                        return $this->formatResponseForRole($baseResponse, $role);
                    }
                }
            }
        }

        return null;
    }

    /**
     * Obtener conocimiento relevante para incluir en el prompt
     */
    private function getRelevantKnowledge($message) {
        $message = strtolower($this->removeAccents($message));
        $relevant = [];

        $topicKeywords = [
            'bici' => ['cuidado_basico', 'frenos', 'cadena'],
            'segur' => ['casco', 'circular', 'noche'],
            'programa' => ['que_es_tubi', 'requisitos', 'como_obtener'],
            'punto' => ['puntos', 'retos', 'logros'],
            'reto' => ['retos', 'puntos'],
            'entreg' => ['estados_bici', 'como_obtener'],
        ];

        foreach ($topicKeywords as $keyword => $topics) {
            if (strpos($message, $keyword) !== false) {
                foreach ($topics as $topic) {
                    if (isset($this->knowledgeBase[$topic])) {
                        $relevant[] = $this->knowledgeBase[$topic];
                    }
                }
            }
        }

        return !empty($relevant) ? implode("\n\n", array_unique($relevant)) : '';
    }

    /**
     * Formatear respuesta según el rol
     */
    private function formatResponseForRole($response, $role) {
        switch ($role) {
            case 'alumno':
                // Más informal y con emojis
                return "¡Hola! 😊 " . $response . "\n\n¿Hay algo más en lo que pueda ayudarte?";

            case 'tutor':
                return "Estimado/a tutor/a,\n\n" . $response . "\n\nSi tiene más consultas, estoy a su disposición.";

            case 'escuela':
                return "Información para la institución:\n\n" . $response . "\n\nPara gestiones adicionales, contacte a la Secretaría de Transporte.";

            case 'proveedor':
                return "Información técnica:\n\n" . $response . "\n\nPara soporte adicional, contacte al administrador del sistema.";

            case 'admin':
                return "📋 " . $response;

            default:
                return $response;
        }
    }

    /**
     * Remover acentos para búsqueda
     */
    private function removeAccents($string) {
        $unwanted = ['á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u', 'ñ'=>'n', 'ü'=>'u'];
        return strtr($string, $unwanted);
    }

    /**
     * Respuestas de fallback mejoradas
     */
    private function getFallbackResponse($message, $role) {
        $message = strtolower($this->removeAccents($message));

        // Saludos
        if (preg_match('/^(hola|buenas|buen dia|buenos dias|hey|que tal)/i', $message)) {
            return $this->getWelcomeMessage($role);
        }

        // Agradecimiento
        if (preg_match('/(gracias|muchas gracias|te agradezco)/i', $message)) {
            $responses = [
                'alumno' => "¡De nada! 😊 Me alegra poder ayudarte. Si tenés más dudas sobre tu TuBi, ¡acá estoy!",
                'tutor' => "Con gusto. Cualquier otra consulta sobre el programa, no dude en preguntar.",
                'escuela' => "Es un placer asistirlo. Quedamos a disposición para cualquier gestión adicional.",
                'proveedor' => "A su disposición. Éxitos con la producción.",
                'admin' => "De nada. Cualquier otra consulta técnica, aquí estaré."
            ];
            return $responses[$role] ?? $responses['alumno'];
        }

        // Despedida
        if (preg_match('/(chau|adios|hasta luego|nos vemos)/i', $message)) {
            $responses = [
                'alumno' => "¡Chau! 👋 ¡Que disfrutes andar en tu TuBi! Recordá siempre usar el casco 🚲",
                'tutor' => "Hasta pronto. Gracias por usar el asistente TuBi.",
                'escuela' => "Hasta pronto. Éxitos con la gestión del programa.",
                'proveedor' => "Hasta pronto. Éxitos en la producción.",
                'admin' => "Hasta luego. Sistema TuBi a su servicio."
            ];
            return $responses[$role] ?? $responses['alumno'];
        }

        // Respuestas específicas por rol
        $roleResponses = [
            'alumno' => "¡Mmm, no estoy seguro de eso! 🤔\n\nPuedo ayudarte con:\n• 🚲 Cuidado de tu bici\n• 🛡️ Seguridad vial y uso del casco\n• 🎮 Retos y puntos\n• 📚 Dudas del programa TuBi\n\n¿Probamos con alguno de esos temas?",

            'tutor' => "Disculpe, no tengo información específica sobre eso.\n\nPuedo ayudarle con:\n• Estado de entrega de bicicletas\n• Documentación necesaria\n• Responsabilidades del programa\n\n¿En qué más puedo asistirlo?",

            'escuela' => "No dispongo de información específica sobre esa consulta.\n\nPuedo asistirle con:\n• Gestión de entregas\n• Asignación de bicicletas\n• Reportes del programa\n\nPara consultas especiales, contacte a la Secretaría de Transporte.",

            'proveedor' => "No tengo información sobre esa consulta específica.\n\nPuedo ayudarle con:\n• Proceso de armado\n• Sistema de códigos QR\n• Suministro a escuelas\n\nPara otras consultas, contacte al administrador.",

            'admin' => "No tengo datos específicos sobre esa consulta.\n\nPuedo asistirle con:\n• Métricas y estadísticas\n• Gestión de usuarios\n• Configuración del sistema\n• Base de conocimiento IA\n\n¿Qué información necesita?"
        ];

        return $roleResponses[$role] ?? $roleResponses['alumno'];
    }

    /**
     * Obtener mensaje de bienvenida por rol
     */
    public function getWelcomeMessage($role) {
        $messages = [
            'alumno' => "¡Hola! 👋 Soy TuBi, tu asistente del programa Tu Bicicleta de San Luis.\n\n¿En qué puedo ayudarte hoy?\n\n• 🚲 **Cuidado de tu bici** - mantenimiento, limpieza, reparaciones\n• 🛡️ **Seguridad vial** - casco, señales, cómo circular\n• 🎮 **Retos y puntos** - cómo ganar y progresar\n• 📚 **El programa** - dudas generales\n\n¡Preguntame lo que quieras!",

            'tutor' => "Buen día, soy el asistente TuBi para tutores y padres.\n\nPuedo ayudarle con:\n\n• 📋 **Estado de entrega** - seguimiento de bicicletas\n• 📄 **Documentación** - requisitos y trámites\n• 📊 **Progreso** - avance de su representado\n• ❓ **Consultas** - del programa en general\n\n¿En qué puedo asistirlo?",

            'escuela' => "Buen día, soy el asistente TuBi para instituciones educativas.\n\nPuedo ayudarle con:\n\n• ✅ **Asignación** - vincular bicicletas a alumnos\n• 📊 **Reportes** - estadísticas de entregas\n• 🔄 **Gestión** - recepción y entregas\n• ❓ **Consultas** - procedimientos del programa\n\n¿En qué puedo asistirlo?",

            'proveedor' => "Buen día, soy el asistente TuBi para proveedores.\n\nPuedo ayudarle con:\n\n• 🔧 **Armado** - proceso y pasos\n• 📱 **Sistema QR** - escaneo y registro\n• 🚚 **Suministro** - entregas a escuelas\n• 📊 **Reportes** - producción y métricas\n\n¿En qué puedo asistirlo?",

            'admin' => "Buen día, soy TuBi Admin.\n\nPuedo ayudarle con:\n\n• 📈 **Métricas** - estadísticas del programa\n• 👥 **Usuarios** - gestión de roles y accesos\n• ⚙️ **Configuración** - ajustes del sistema\n• 🤖 **Base IA** - documentos y conocimiento\n• 🔍 **Análisis** - consultas de datos\n\n¿Qué necesita?"
        ];

        return $messages[$role] ?? $messages['alumno'];
    }
}
