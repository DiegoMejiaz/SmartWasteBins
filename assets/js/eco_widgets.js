/**
 * SMART WASTE BIN (SWB) - ECO WIDGETS & SYSTEM FEEDBACK
 * Chatbot Gemini (EcoBot AI), Modales de Soporte, Testimonios, Carné QR y Pantallas de Carga
 * Desarrollado para UNICOLOMBO - Ingeniería de Sistemas
 */

(function() {
    'use strict';

    // 1. Inyectar HTML de los Widgets al cargar la página
    function initEcoWidgets() {
        if (document.getElementById('ecoLoadingOverlay')) return; // Ya inicializado

        const widgetsHTML = `
        <!-- Overlay de Pantalla de Carga (Loading Screen) -->
        <div id="ecoLoadingOverlay">
            <div class="eco-spinner-container">
                <div class="eco-spinner-ring"></div>
                <div class="eco-spinner-icon">♻️</div>
            </div>
            <div id="ecoLoadingText">Procesando solicitud...</div>
            <div id="ecoLoadingSubtext">Sincronizando con la red Smart Waste Bin</div>
        </div>

        <!-- Botón Flotante del Chatbot Gemini -->
        <div class="eco-chatbot-fab" id="ecoChatbotFab" title="Hablar con EcoBot Gemini">
            <span style="font-size: 1.8rem;">🤖</span>
            <span class="eco-chatbot-badge"></span>
        </div>

        <!-- Ventana del Chatbot Gemini -->
        <div class="eco-chat-window" id="ecoChatWindow">
            <div class="eco-chat-header">
                <div class="eco-chat-title">
                    <span>🌿</span>
                    <div>
                        <div>EcoBot Gemini</div>
                        <div class="eco-chat-status">En línea · Asistente IA</div>
                    </div>
                </div>
                <button class="eco-chat-close" id="ecoChatClose">&times;</button>
            </div>
            <div class="eco-chat-body" id="ecoChatBody">
                <div class="eco-msg eco-msg-bot">
                    ¡Hola! 👋 Soy <strong>EcoBot Gemini</strong>, tu asistente de reciclaje inteligente en <strong>Smart Waste Bin</strong>. ¿En qué puedo ayudarte hoy?
                </div>
                <div class="eco-chips-container" id="ecoChips">
                    <span class="eco-chip" onclick="window.enviarPreguntaRapida('¿Cuántos puntos gano por material?')">♻️ Puntos por material</span>
                    <span class="eco-chip" onclick="window.enviarPreguntaRapida('¿Cómo canjeo mis puntos a dinero?')">💸 Canjear a dinero COP</span>
                    <span class="eco-chip" onclick="window.enviarPreguntaRapida('¿Dónde están ubicadas las papeleras?')">📍 Ubicación de Bins</span>
                    <span class="eco-chip" onclick="window.enviarPreguntaRapida('¿Cuál es la capacidad máxima?')">⚖️ Capacidad (50 kg)</span>
                    <span class="eco-chip" onclick="window.enviarPreguntaRapida('¿Cómo funciona el código QR?')">📱 Carné QR</span>
                </div>
            </div>
            <div class="eco-chat-footer">
                <input type="text" class="eco-chat-input" id="ecoChatInput" placeholder="Escribe tu duda sobre reciclaje..." autocomplete="off">
                <button class="eco-chat-send" id="ecoChatSend">➤</button>
            </div>
        </div>

        <!-- Modal de Soporte y Contacto -->
        <div class="eco-modal-backdrop" id="ecoModalSoporte">
            <div class="eco-modal-card">
                <button class="eco-modal-close" onclick="window.cerrarEcoModal('ecoModalSoporte')">&times;</button>
                <div class="eco-modal-title"><span>📞</span> Soporte y Contacto</div>
                <div class="eco-modal-subtitle">¿Tienes inconvenientes con un depósito, canje de puntos o tu cuenta? Comunícate con nuestra mesa de ayuda oficial.</div>
                
                <div style="background: rgba(255,255,255,0.08); padding: 15px; border-radius: 14px; margin-bottom: 15px; border: 1px solid rgba(255,255,255,0.15);">
                    <div style="margin-bottom: 8px;"><strong>🏢 Institución:</strong> Fundación Universitaria Colombo Internacional (UNICOLOMBO)</div>
                    <div style="margin-bottom: 8px;"><strong>👨‍💻 Líder Técnico:</strong> Klever Polo Bolívar & Equipo SWB 6to Semestre</div>
                    <div style="margin-bottom: 8px;"><strong>✉️ Correo:</strong> soporte.smartbin@unicolombo.edu.co</div>
                    <div style="margin-bottom: 8px;"><strong>🕒 Horario:</strong> Lun a Vie 8:00 AM - 6:00 PM · Sáb 8:00 AM - 12:00 M</div>
                </div>

                <a href="https://wa.me/573001234567?text=Hola,%20solicito%20asistencia%20t%C3%A9cnica%20con%20la%20plataforma%20Smart%20Waste%20Bin" target="_blank" class="eco-btn-action eco-btn-whatsapp">
                    <span>💬</span> Chatear con Soporte por WhatsApp
                </a>
            </div>
        </div>

        <!-- Modal de Testimonios y Calificaciones (Experiencia de Usuario) -->
        <div class="eco-modal-backdrop" id="ecoModalTestimonios">
            <div class="eco-modal-card">
                <button class="eco-modal-close" onclick="window.cerrarEcoModal('ecoModalTestimonios')">&times;</button>
                <div class="eco-modal-title"><span>⭐</span> Experiencia y Testimonios</div>
                <div class="eco-modal-subtitle">Conoce la opinión de la comunidad universitaria o califica tu experiencia con las papeleras inteligentes.</div>
                
                <div style="text-align: center; margin-bottom: 15px;">
                    <div style="font-size: 2.5rem; font-weight: 800; color: #f1c40f;">4.9 <span style="font-size: 1.2rem; color: rgba(255,255,255,0.8);">/ 5.0</span></div>
                    <div class="eco-stars">⭐⭐⭐⭐⭐</div>
                    <div style="font-size: 0.85rem; color: rgba(255,255,255,0.7);">Basado en 128 calificaciones de estudiantes y docentes</div>
                </div>

                <!-- Testimonios de muestra -->
                <div class="eco-testimonial-item">
                    <div class="eco-stars">⭐⭐⭐⭐⭐</div>
                    <div class="eco-testimonial-text">"Increíble iniciativa en el campus. Ya canjeé $25.000 COP a mi Nequi solo reciclando botellas plásticas del almuerzo."</div>
                    <div class="eco-testimonial-author">— Camila Torres (Estudiante UNICOLOMBO)</div>
                </div>

                <div class="eco-testimonial-item">
                    <div class="eco-stars">⭐⭐⭐⭐⭐</div>
                    <div class="eco-testimonial-text">"El pesaje con sensores HX711 es súper rápido y transparente. La aplicación móvil y la web se sincronizan al instante."</div>
                    <div class="eco-testimonial-author">— Juan Camilo Puello (Ing. de Sistemas)</div>
                </div>

                <!-- Formulario interactivo para calificar -->
                <div style="background: rgba(46, 204, 113, 0.15); border: 1px solid rgba(46, 204, 113, 0.3); padding: 15px; border-radius: 14px; margin-top: 15px;">
                    <div style="font-weight: 600; text-align: center;">¿Cómo calificarías tu experiencia hoy?</div>
                    <div class="eco-star-rating" id="ecoStarRating">
                        <span data-star="1">★</span>
                        <span data-star="2">★</span>
                        <span data-star="3">★</span>
                        <span data-star="4">★</span>
                        <span data-star="5" class="active">★</span>
                    </div>
                    <textarea id="ecoFeedbackText" placeholder="Escribe un breve comentario de tu experiencia..." style="width: 100%; box-sizing: border-box; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; color: white; padding: 8px; font-family: inherit; font-size: 0.9rem; resize: none; height: 60px;"></textarea>
                    <button class="eco-btn-action" onclick="window.enviarCalificacion()">Enviar Calificación</button>
                </div>
            </div>
        </div>

        <!-- Modal de Carné QR de Reciclaje -->
        <div class="eco-modal-backdrop" id="ecoModalQR">
            <div class="eco-modal-card" style="text-align: center;">
                <button class="eco-modal-close" onclick="window.cerrarEcoModal('ecoModalQR')">&times;</button>
                <div class="eco-modal-title" style="justify-content: center;"><span>📱</span> Carné Digital QR</div>
                <div class="eco-modal-subtitle">Presenta este código en el lector de la estación SmartBin para identificarte y sumar puntos automáticamente.</div>
                
                <div class="eco-qr-card">
                    <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; color: #27ae60; font-weight: 700;">UNICOLOMBO · ECO-CIUDADANO</div>
                    <img id="ecoQrImage" class="eco-qr-image" src="" alt="Carné QR">
                    <div id="ecoQrName" class="eco-qr-holder">Usuario</div>
                    <div id="ecoQrId" class="eco-qr-id">ID: 00000</div>
                    <div style="font-size: 0.75rem; color: #95a5a6; margin-top: 5px;">Válido en todas las estaciones de Cartagena de Indias</div>
                </div>

                <button class="eco-btn-action" onclick="window.print()">
                    <span>🖨️</span> Imprimir o Guardar Carné
                </button>
            </div>
        </div>
        `;

        document.body.insertAdjacentHTML('beforeend', widgetsHTML);
        setupChatbotEvents();
        setupStarRatingEvents();
        setupFormInterceptors();
    }

    // 2. Control de la Pantalla de Carga
    window.showEcoLoader = function(title, subtitle) {
        const overlay = document.getElementById('ecoLoadingOverlay');
        const text = document.getElementById('ecoLoadingText');
        const sub = document.getElementById('ecoLoadingSubtext');
        if (text && title) text.innerText = title;
        if (sub && subtitle) sub.innerText = subtitle;
        if (overlay) overlay.style.display = 'flex';
    };

    window.hideEcoLoader = function() {
        const overlay = document.getElementById('ecoLoadingOverlay');
        if (overlay) overlay.style.display = 'none';
    };

    // 3. Modales
    window.abrirEcoModal = function(id) {
        const m = document.getElementById(id);
        if (m) m.style.display = 'flex';
    };

    window.cerrarEcoModal = function(id) {
        const m = document.getElementById(id);
        if (m) m.style.display = 'none';
    };

    window.abrirModalQR = function(nombre, idUsuario) {
        const qrImg = document.getElementById('ecoQrImage');
        const qrName = document.getElementById('ecoQrName');
        const qrId = document.getElementById('ecoQrId');
        
        const dataPayload = encodeURIComponent(`SWB_USER_${idUsuario}_${nombre}`);
        if (qrImg) qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${dataPayload}`;
        if (qrName) qrName.innerText = nombre || 'Ciudadano Reciclador';
        if (qrId) qrId.innerText = `ID de Usuario: ${idUsuario || '001'}`;

        window.abrirEcoModal('ecoModalQR');
    };

    window.enviarCalificacion = function() {
        const feedback = document.getElementById('ecoFeedbackText').value;
        window.cerrarEcoModal('ecoModalTestimonios');
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '¡Muchas Gracias!',
                text: 'Tu testimonio y calificación han sido registrados con éxito en la plataforma.',
                confirmButtonColor: '#27ae60'
            });
        } else {
            alert('¡Muchas Gracias! Tu testimonio ha sido registrado con éxito.');
        }
    };

    // 4. Lógica de Chatbot Gemini Inteligente
    function setupChatbotEvents() {
        const fab = document.getElementById('ecoChatbotFab');
        const win = document.getElementById('ecoChatWindow');
        const closeBtn = document.getElementById('ecoChatClose');
        const sendBtn = document.getElementById('ecoChatSend');
        const input = document.getElementById('ecoChatInput');

        if (fab && win) {
            fab.addEventListener('click', () => {
                const isHidden = win.style.display === 'none' || !win.style.display;
                win.style.display = isHidden ? 'flex' : 'none';
                if (isHidden) input.focus();
            });
        }

        if (closeBtn && win) {
            closeBtn.addEventListener('click', () => {
                win.style.display = 'none';
            });
        }

        if (sendBtn && input) {
            sendBtn.addEventListener('click', () => procesarMensajeUsuario());
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') procesarMensajeUsuario();
            });
        }
    }

    window.enviarPreguntaRapida = function(texto) {
        const input = document.getElementById('ecoChatInput');
        if (input) {
            input.value = texto;
            procesarMensajeUsuario();
        }
    };

    function procesarMensajeUsuario() {
        const input = document.getElementById('ecoChatInput');
        const chatBody = document.getElementById('ecoChatBody');
        const texto = input.value.trim();
        if (!texto) return;

        // Agregar mensaje del usuario
        const userMsg = document.createElement('div');
        userMsg.className = 'eco-msg eco-msg-user';
        userMsg.innerText = texto;
        chatBody.appendChild(userMsg);
        input.value = '';
        chatBody.scrollTop = chatBody.scrollHeight;

        // Respuesta simulada con IA Gemini SWB
        setTimeout(() => {
            const botMsg = document.createElement('div');
            botMsg.className = 'eco-msg eco-msg-bot';
            botMsg.innerHTML = responderPreguntaEco(texto);
            chatBody.appendChild(botMsg);
            chatBody.scrollTop = chatBody.scrollHeight;
        }, 600);
    }

    function responderPreguntaEco(q) {
        const query = q.toLowerCase();

        if (query.includes('punto') || query.includes('material') || query.includes('factor') || query.includes('cuanto')) {
            return `♻️ <strong>Tabla de Puntos por Kilogramo:</strong><br/>
            • <strong>Plástico:</strong> 10 puntos/kg<br/>
            • <strong>Vidrio / Orgánico:</strong> 15 puntos/kg<br/>
            • <strong>Electrónicos:</strong> 50 puntos/kg (¡el de mayor valor!)<br/>
            • <strong>Papel y Cartón:</strong> 5 puntos/kg<br/>
            <em>Fórmula: Puntos = Redondear(peso_en_kg × factor)</em>`;
        }

        if (query.includes('canj') || query.includes('dinero') || query.includes('nequi') || query.includes('daviplata') || query.includes('retir')) {
            return `💵 <strong>Tasa de Canje Oficial:</strong><br/>
            • <strong>10 Puntos = $100 COP</strong> en efectivo.<br/>
            • Puedes retirar directamente a tu cuenta <strong>Nequi</strong> o <strong>Daviplata</strong> ingresando tu número celular (debe tener 10 dígitos y empezar por 3).<br/>
            • La transacción se procesa de forma inmediata y descuenta tu saldo de manera segura.`;
        }

        if (query.includes('donde') || query.includes('ubicaci') || query.includes('papelera') || query.includes('bin') || query.includes('mapa')) {
            return `📍 <strong>Papeleras Inteligentes en Cartagena:</strong><br/>
            Contamos con 8 estaciones sensorizadas en el Centro Histórico, incluyendo:<br/>
            1. Hotel Casa Don Luis (Calle del Cuartel)<br/>
            2. Torre del Reloj Público<br/>
            3. Plaza Santo Domingo<br/>
            4. Parque de Bolívar<br/>
            Puedes ver su ubicación en tiempo real en la opción <strong>Mapa de Bins</strong>.`;
        }

        if (query.includes('capacidad') || query.includes('limite') || query.includes('50') || query.includes('bloque')) {
            return `⚖️ <strong>Capacidad y Seguridad:</strong><br/>
            • Cada SmartBin tiene una <strong>capacidad máxima fija de 50.0 kg</strong>.<br/>
            • Si una papelera alcanza los 50 kg, se <strong>bloquea automáticamente</strong> para evitar desbordes y notifica a la cuadrilla de aseo para su vaciado.`;
        }

        if (query.includes('qr') || query.includes('carne') || query.includes('carné') || query.includes('lector')) {
            return `📱 <strong>Carné Digital QR:</strong><br/>
            • Cada usuario registrado cuenta con un <strong>código QR único</strong> asociado a su ID.<br/>
            • Puedes mostrarlo directamente desde la opción <em>'Mi Carné QR'</em> en tu panel para que la estación SmartBin te reconozca al instante.`;
        }

        if (query.includes('soporte') || query.includes('contacto') || query.includes('ayuda') || query.includes('error')) {
            return `📞 <strong>Mesa de Ayuda y Soporte:</strong><br/>
            ¿Necesitas ayuda técnica? Puedes escribirnos directamente a:<br/>
            • Correo: <code>soporte.smartbin@unicolombo.edu.co</code><br/>
            • WhatsApp de Soporte: <a href="https://wa.me/573001234567" target="_blank" style="color: #2ecc71; text-decoration: underline;">Abrir WhatsApp</a>`;
        }

        // Respuesta genérica inteligente
        return `🤖 Gracias por tu consulta. En <strong>Smart Waste Bin</strong> transformamos tus residuos reciclables en puntos redimibles por dinero real en Cartagena. ¿Deseas consultar sobre los puntos por material, ubicaciones de papeleras o cómo retirar dinero a Nequi?`;
    }

    function setupStarRatingEvents() {
        document.addEventListener('click', function(e) {
            if (e.target && e.target.parentElement && e.target.parentElement.id === 'ecoStarRating') {
                const starVal = parseInt(e.target.getAttribute('data-star') || '5');
                const stars = e.target.parentElement.querySelectorAll('span');
                stars.forEach((s, idx) => {
                    if (idx < starVal) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            }
        });
    }

    function setupFormInterceptors() {
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form && (form.classList.contains('eco-loading-form') || form.action.includes('procesar_') || form.action.includes('login') || form.action.includes('insertar'))) {
                window.showEcoLoader("Procesando transacción...", "Conectando con la red Smart Waste Bin");
            }
        });
    }

    // Inicializar al cargar el DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEcoWidgets);
    } else {
        initEcoWidgets();
    }
})();


// Mostrar u ocultar contraseña y comprobar la confirmación (formularios de acceso y registro)
document.addEventListener("click", function (ev) {
    const boton = ev.target.closest("[data-ver-clave]");
    if (!boton) return;
    const campo = boton.parentElement.querySelector("input");
    const mostrar = campo.type === "password";
    campo.type = mostrar ? "text" : "password";
    boton.textContent = mostrar ? "Ocultar" : "Ver";
    boton.setAttribute("aria-label", mostrar ? "Ocultar contraseña" : "Mostrar contraseña");
});
document.querySelectorAll("[data-confirma]").forEach(function (confirmar) {
    const original = document.getElementById(confirmar.dataset.confirma);
    const validar = function () {
        confirmar.setCustomValidity(confirmar.value === original.value ? "" : "Las contraseñas no coinciden");
    };
    confirmar.addEventListener("input", validar);
    original.addEventListener("input", validar);
});
