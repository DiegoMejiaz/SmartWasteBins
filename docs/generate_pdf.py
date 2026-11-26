# -*- coding: utf-8 -*-
"""Generador de Manual Técnico y de Usuario en PDF para Smart Waste Bin (SWB)
Formato oficial SENA (FAVA/CIMI) e IEEE 1063-2001
UNICOLOMBO - Ingeniería de Sistemas 6to Semestre
"""
import os
import sys
from PIL import Image as PILImage
from reportlab.lib.pagesizes import letter
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Table, TableStyle, Spacer, PageBreak, Image, KeepTogether, HRFlowable
)
from reportlab.pdfgen import canvas

class NumberedCanvas(canvas.Canvas):
    """Canvas de dos pasadas para calcular el número total de páginas (Página X de Y)"""
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_header_footer(num_pages)
            super().showPage()
        super().save()

    def draw_header_footer(self, page_count):
        if self._pageNumber > 1:
            # Header
            self.setFont("Helvetica", 7.5)
            self.setFillColor(colors.HexColor("#4a5568"))
            self.drawString(36, 762, "SMART WASTE BIN (SWB) — PROYECTO DE AULA 6TO SEMESTRE")
            self.drawRightString(576, 762, "UNICOLOMBO — INGENIERÍA DE SISTEMAS")
            self.setStrokeColor(colors.HexColor("#cbd5e1"))
            self.setLineWidth(0.6)
            self.line(36, 756, 576, 756)

            # Footer
            self.line(36, 36, 576, 36)
            self.setFont("Helvetica", 7.5)
            self.drawString(36, 25, "Manual Técnico y de Usuario — Norma IEEE 1063-2001 / SENA CIMI")
            self.drawRightString(576, 25, f"Página {self._pageNumber} de {page_count}")


def build_pdf(filename):
    doc = SimpleDocTemplate(
        filename,
        pagesize=letter,
        leftMargin=36,
        rightMargin=36,
        topMargin=46,
        bottomMargin=46
    )

    styles = getSampleStyleSheet()

    # Paleta de colores institucional
    PRIMARY = colors.HexColor("#27ae60")     # Verde esmeralda institucional
    DARK_GREEN = colors.HexColor("#1b5e20")  # Verde oscuro
    TEXT_DARK = colors.HexColor("#1e293b")   # Slate oscuro
    LIGHT_BG = colors.HexColor("#f8fafc")    # Gris neutro muy suave
    ALT_ROW = colors.HexColor("#f1f5f9")     # Gris alterno
    BORDER_COLOR = colors.HexColor("#cbd5e1")# Borde suave
    ACCENT_BLUE = colors.HexColor("#2563eb") # Azul acento

    # Estilos de texto
    title_style = ParagraphStyle(
        'CoverTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=24,
        leading=28,
        textColor=DARK_GREEN,
        alignment=1, # Centrado
    )
    subtitle_style = ParagraphStyle(
        'CoverSubTitle',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=12,
        leading=16,
        textColor=TEXT_DARK,
        alignment=1,
    )
    meta_style = ParagraphStyle(
        'CoverMeta',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9.5,
        leading=14,
        textColor=colors.HexColor("#475569"),
        alignment=1,
    )
    h1_style = ParagraphStyle(
        'Heading1_Custom',
        parent=styles['Heading1'],
        fontName='Helvetica-Bold',
        fontSize=15,
        leading=18,
        textColor=DARK_GREEN,
        spaceBefore=14,
        spaceAfter=8,
        keepWithNext=True
    )
    h2_style = ParagraphStyle(
        'Heading2_Custom',
        parent=styles['Heading2'],
        fontName='Helvetica-Bold',
        fontSize=12,
        leading=15,
        textColor=TEXT_DARK,
        spaceBefore=10,
        spaceAfter=6,
        keepWithNext=True
    )
    h3_style = ParagraphStyle(
        'Heading3_Custom',
        parent=styles['Heading3'],
        fontName='Helvetica-Bold',
        fontSize=10,
        leading=13,
        textColor=PRIMARY,
        spaceBefore=8,
        spaceAfter=4,
        keepWithNext=True
    )
    body_style = ParagraphStyle(
        'Body_Custom',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=12,
        textColor=TEXT_DARK,
        spaceBefore=3,
        spaceAfter=5
    )
    body_bold = ParagraphStyle(
        'Body_Bold_Custom',
        parent=body_style,
        fontName='Helvetica-Bold'
    )
    callout_style = ParagraphStyle(
        'Callout_Text',
        parent=styles['Normal'],
        fontName='Helvetica-Oblique',
        fontSize=8,
        leading=11.5,
        textColor=colors.HexColor("#0f172a")
    )
    table_header = ParagraphStyle(
        'TableHeader',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8,
        leading=10,
        textColor=colors.white,
        alignment=0
    )
    table_cell = ParagraphStyle(
        'TableCell',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=7.5,
        leading=10,
        textColor=TEXT_DARK
    )
    table_cell_bold = ParagraphStyle(
        'TableCellBold',
        parent=table_cell,
        fontName='Helvetica-Bold'
    )
    code_style = ParagraphStyle(
        'CodeStyle',
        parent=styles['Normal'],
        fontName='Courier',
        fontSize=7.5,
        leading=9.5,
        textColor=colors.HexColor("#0f172a")
    )

    story = []

    def make_callout(text, title="NOTA IMPORTANTE", border_color=PRIMARY, bg_color=colors.HexColor("#f0fdf4")):
        content = [
            Paragraph(f"<b>{title}:</b> {text}", callout_style)
        ]
        t = Table([[content]], colWidths=[540])
        t.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,-1), bg_color),
            ('BOX', (0,0), (-1,-1), 1, border_color),
            ('TOPPADDING', (0,0), (-1,-1), 5),
            ('BOTTOMPADDING', (0,0), (-1,-1), 5),
            ('LEFTPADDING', (0,0), (-1,-1), 8),
            ('RIGHTPADDING', (0,0), (-1,-1), 8),
        ]))
        return t

    def styled_table(data, col_widths, is_header=True):
        """Genera una tabla ReportLab con diseño institucional limpio y alternancia de filas"""
        formatted_data = []
        for r_idx, row in enumerate(data):
            formatted_row = []
            for c_idx, cell in enumerate(row):
                if isinstance(cell, str):
                    if r_idx == 0 and is_header:
                        p = Paragraph(cell, table_header)
                    elif c_idx == 0 and len(row) > 2:
                        p = Paragraph(cell, table_cell_bold)
                    else:
                        p = Paragraph(cell, table_cell)
                    formatted_row.append(p)
                else:
                    formatted_row.append(cell)
            formatted_data.append(formatted_row)

        t = Table(formatted_data, colWidths=col_widths, repeatRows=1 if is_header else 0)
        t_style = [
            ('GRID', (0,0), (-1,-1), 0.5, BORDER_COLOR),
            ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
            ('TOPPADDING', (0,0), (-1,-1), 4),
            ('BOTTOMPADDING', (0,0), (-1,-1), 4),
            ('LEFTPADDING', (0,0), (-1,-1), 5),
            ('RIGHTPADDING', (0,0), (-1,-1), 5),
        ]
        if is_header:
            t_style.append(('BACKGROUND', (0,0), (-1,0), PRIMARY))
        for r_idx in range(1 if is_header else 0, len(data)):
            bg = ALT_ROW if r_idx % 2 == 1 else colors.white
            t_style.append(('BACKGROUND', (0, r_idx), (-1, r_idx), bg))

        t.setStyle(TableStyle(t_style))
        return t

    # -------------------------------------------------------------------------
    # 1. PORTADA OFICIAL
    # -------------------------------------------------------------------------
    story.append(Spacer(1, 20))
    story.append(Paragraph("FUNDACIÓN UNIVERSITARIA COLOMBO INTERNACIONAL — UNICOLOMBO", meta_style))
    story.append(Paragraph("FACULTAD DE INGENIERÍA — PROGRAMA DE INGENIERÍA DE SISTEMAS", meta_style))
    story.append(Spacer(1, 25))

    # Recuadro con borde temático verde
    banner_content = [
        [Paragraph("PROYECTO DE AULA — 6TO SEMESTRE", ParagraphStyle('BSub', parent=subtitle_style, fontName='Helvetica-Bold', textColor=PRIMARY))],
        [Spacer(1, 8)],
        [Paragraph("MANUAL TÉCNICO Y DE USUARIO", title_style)],
        [Spacer(1, 6)],
        [Paragraph("SISTEMA DE GESTIÓN Y GAMIFICACIÓN DE PAPELERAS INTELIGENTES<br/><b>SMART WASTE BIN (SWB) — ECO FRIENDLY</b>", subtitle_style)],
    ]
    banner_table = Table(banner_content, colWidths=[520])
    banner_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#f0fdf4")),
        ('BOX', (0,0), (-1,-1), 1.5, PRIMARY),
        ('ALIGN', (0,0), (-1,-1), 'CENTER'),
        ('TOPPADDING', (0,0), (-1,-1), 14),
        ('BOTTOMPADDING', (0,0), (-1,-1), 14),
    ]))
    story.append(banner_table)

    story.append(Spacer(1, 35))

    # Metadatos del proyecto
    meta_table_data = [
        ["DATOS GENERALES DE LA ENTREGA", ""],
        ["Proyecto Académico:", "Smart Waste Bin (SWB) — Gestión de Residuos Urbanos e IoT"],
        ["Asignatura / Módulo:", "Proyecto de Aula / Análisis y Diseño de Software II"],
        ["Equipo de Autores:", "Klever Adriano Polo Bolívar\nJuan Caro Ramírez\nDiego Mejía Espinosa\nRobert Pérez Maza"],
        ["Docente Orientador:", "Ing. Juan José Puello Beltrán"],
        ["Referencia Metodológica:", "Estándar IEEE 1063-2001 & Guía FAVA / SENA Centro CIMI"],
        ["Ubicación y Fecha:", "Cartagena de Indias D.T. y C., Octubre de 2026"],
        ["Versión del Software:", "Versión 2.0 (Stack LAMP / WAMP - MariaDB - PHP 8.2)"]
    ]
    formatted_meta = []
    for r_idx, row in enumerate(meta_table_data):
        if r_idx == 0:
            formatted_meta.append([
                Paragraph(f"<b>{row[0]}</b>", ParagraphStyle('MH', parent=table_header, alignment=1)),
                Paragraph("", table_header)
            ])
        else:
            p_val = Paragraph(row[1].replace("\n", "<br/>"), table_cell)
            formatted_meta.append([Paragraph(f"<b>{row[0]}</b>", table_cell), p_val])

    t_meta = Table(formatted_meta, colWidths=[150, 370])
    t_meta.setStyle(TableStyle([
        ('SPAN', (0,0), (1,0)),
        ('BACKGROUND', (0,0), (1,0), DARK_GREEN),
        ('GRID', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('TOPPADDING', (0,0), (-1,-1), 4),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
        ('BACKGROUND', (0,1), (0,-1), colors.HexColor("#f8fafc")),
    ]))
    story.append(t_meta)

    story.append(PageBreak())

    # -------------------------------------------------------------------------
    # TABLA DE CONTENIDO E INTRODUCCIÓN
    # -------------------------------------------------------------------------
    story.append(Paragraph("Estructura de Contenidos", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=8))

    toc_data = [
        ["Sección / Capítulo", "Pág."],
        ["Introducción y Planteamiento del Problema en Cartagena", "3"],
        ["PARTE I: MANUAL DEL USUARIO", "4"],
        ["  1.1 Alcance del Manual de Usuario", "4"],
        ["  1.2 Requisitos de Conocimiento para Manejar el Sistema", "4"],
        ["  1.3 Requerimientos de Hardware y Software (Equipo Cliente)", "4"],
        ["  1.4 Política de Seguridad y Privacidad", "5"],
        ["  1.5 Ingreso y Autenticación en el Sistema", "5"],
        ["  1.6 Mapa de Navegación y Elementos de Interfaz Glassmorphism", "5"],
        ["  1.7 Descripción de Funciones del Sistema Paso a Paso (Pantallas y Controles)", "6"],
        ["  1.8 Mesa de Ayuda y Procedimientos de Soporte", "8"],
        ["  1.9 Catálogo y Resolución de Mensajes de Error", "8"],
        ["PARTE II: MANUAL TÉCNICO DE LA APLICACIÓN", "9"],
        ["  2.1 Alcance del Manual Técnico y Objetivos", "9"],
        ["  2.2 Descripción de Procesos, Requerimientos (RF/RNF) y Reglas de Negocio", "9"],
        ["  2.3 Diagramas UML (Contexto, Casos de Uso, Clases, Secuencia y Despliegue)", "10"],
        ["  2.4 Administración de Usuarios y Seguridad de Datos", "13"],
        ["  2.5 Modelo Relacional y Diccionario de Datos Completo (MariaDB)", "14"],
        ["  2.6 Descripción de la Plataforma de Ejecución (LAMP/WAMP, PHP, Apache)", "16"],
        ["  2.7 Documentación del Código Fuente y Catálogo de Programas", "17"],
        ["  2.8 Acuerdos de Niveles de Servicio (ANS / SLA) y Matriz de Escalamiento", "18"],
        ["Glosario de Términos", "19"],
        ["Bibliografía y Normatividad", "20"],
        ["Control del Documento y Registro de Firmas", "20"]
    ]
    story.append(styled_table(toc_data, [480, 60]))
    story.append(Spacer(1, 12))

    story.append(Paragraph("Introducción", h1_style))
    story.append(Paragraph(
        "El desarrollo de soluciones tecnológicas orientadas al bienestar social y ambiental requiere no solo "
        "de un producto de software robusto, sino también de documentación rigurosa que garantice la adopción efectiva "
        "por parte de la ciudadanía y la operabilidad del personal técnico. En concordancia con los lineamientos del SENA "
        "(CIMI - Regional Santander) y el estándar internacional IEEE 1063-2001, este documento consolida el "
        "<b>Manual de Usuario</b> y el <b>Manual Técnico</b> del proyecto de aula <b>Smart Waste Bin (SWB)</b>.",
        body_style
    ))
    story.append(Paragraph(
        "En entornos urbanos y de alta afluencia turística como la ciudad de <b>Cartagena de Indias</b>, el manejo ineficiente "
        "de residuos sólidos y la baja cultura de separación en la fuente representan desafíos críticos. Smart Waste Bin responde "
        "a esta problemática integrando contenedores dotados con sensores ultrasónicos de nivel y celdas de pesaje, una plataforma "
        "web de interacción en tiempo real y un modelo de <b>gamificación basado en eco-puntos</b> canjeables por dinero o beneficios, "
        "incentivando activamente el reciclaje ciudadano y optimizando las rutas de recolección de las empresas de aseo.",
        body_style
    ))

    story.append(PageBreak())

    # -------------------------------------------------------------------------
    # PARTE I: MANUAL DEL USUARIO
    # -------------------------------------------------------------------------
    story.append(Paragraph("PARTE I: MANUAL DEL USUARIO", h1_style))
    story.append(HRFlowable(width="100%", thickness=1.5, color=PRIMARY, spaceAfter=8))

    story.append(Paragraph(
        "El Manual del Usuario describe de forma didáctica, secuencial e ilustrada el conjunto de operaciones que el ciudadano "
        "y el operador pueden realizar en la aplicación web Smart Waste Bin. Su finalidad es despejar cualquier duda operativa y "
        "permitir que el usuario domine el sistema con total fluidez.",
        body_style
    ))

    # 1.1 Alcance
    story.append(Paragraph("1.1 Alcance del Manual de Usuario", h2_style))
    user_scope = [
        ["Ítem", "Detalle"],
        ["Propósito", "Orientar a ciudadanos, recicladores y personal operativo en el acceso, depósito de residuos, acumulación de eco-puntos y canje de beneficios."],
        ["Público Objetivo", "Comunidad de Cartagena de Indias, sector turístico del Centro Histórico y Getsemaní, y operadores urbanos de reciclaje."],
        ["Límites de Aplicación", "Cubre exclusivamente la interacción con la interfaz web y la lógica de depósitos. No incluye mantenimiento electromecánico interno de los sensores."]
    ]
    story.append(styled_table(user_scope, [130, 410]))
    story.append(Spacer(1, 8))

    # 1.2 Requisitos de Conocimiento
    story.append(Paragraph("1.2 Requisitos de Conocimiento para Manejar el Sistema", h2_style))
    story.append(Paragraph("Para hacer uso del sistema, el usuario requiere únicamente conocimientos elementales de:", body_style))
    user_know = [
        ["No", "Conocimiento Requerido", "Nivel"],
        ["1", "Manejo básico de navegadores web (Google Chrome, Firefox, Edge, Safari)", "Básico"],
        ["2", "Navegación e interacción táctil en dispositivos móviles o ratón/teclado en PC", "Básico"],
        ["3", "Conocimiento general de clasificación de residuos (plástico, orgánicos, electrónicos, papel/cartón)", "Elemental"],
        ["4", "Manejo confidencial de datos de acceso personales y cuentas móviles de retiro (Nequi / Daviplata)", "Responsable"]
    ]
    story.append(styled_table(user_know, [30, 410, 100]))
    story.append(Spacer(1, 8))

    # 1.3 Requerimientos HW / SW Cliente
    story.append(Paragraph("1.3 Requerimientos de Hardware y Software (Equipo Cliente)", h2_style))
    story.append(Paragraph("Las especificaciones para operar la aplicación desde el lado cliente son las siguientes:", body_style))
    hw_sw_data = [
        ["Componente", "Requisitos Mínimos", "Requisitos Recomendados"],
        ["Dispositivo", "Smartphone, tableta o PC con acceso a red", "PC Portátil / Smartphone moderno"],
        ["Memoria RAM", "2 GB", "4 GB o superior"],
        ["Resolución de Pantalla", "360 x 640 píxeles (móvil responsivo)", "1366 x 768 píxeles o superior"],
        ["Conectividad", "Conexión 3G / Wi-Fi de 1 Mbps", "Conexión 4G / Wi-Fi de 5 Mbps o superior"],
        ["Sistema Operativo", "Windows 7/10/11, Android 8+, iOS 12+, Linux", "Windows 10/11, Android 11+, iOS 15+"],
        ["Navegador Web", "Chrome 70+, Firefox 65+, Safari 12+, Edge 80+", "Versiones actualizadas con JavaScript habilitado"]
    ]
    story.append(styled_table(hw_sw_data, [100, 220, 220]))
    story.append(Spacer(1, 8))

    # 1.4 Política de Seguridad
    story.append(Paragraph("1.4 Política de Seguridad y Protección de Datos", h2_style))
    story.append(make_callout(
        "El uso de la plataforma implica el estricto cumplimiento de la política de seguridad institucional. "
        "Cada usuario es responsable exclusivo de su cuenta. Se prohíbe el registro de pesos fraudulentos; el sistema cuenta "
        "con auditorías volumétricas automatizadas. Al utilizar terminales de acceso público, debe cerrarse la sesión inmediatamente.",
        title="DIRECTIVA DE SEGURIDAD CIUDADANA",
        border_color=colors.HexColor("#dc2626"),
        bg_color=colors.HexColor("#fef2f2")
    ))

    # 1.5 Ingreso al Sistema
    story.append(Paragraph("1.5 Ingreso al Sistema de Información", h2_style))
    story.append(Paragraph(
        "Para acceder a la plataforma, diríjase en su navegador web a la siguiente dirección URL:<br/>"
        "<b><code>http://localhost/Smart_waste_bin_6to_semestre/index.html</code></b>",
        body_style
    ))
    story.append(Paragraph(
        "En la pantalla de bienvenida con diseño de follaje tropical, elija <b>'Iniciar Sesión'</b> si ya posee cuenta registrada, "
        "o <b>'Registrarse'</b> si es su primera vez en el sistema.",
        body_style
    ))

    # 1.6 Mapa de Navegación
    story.append(Paragraph("1.6 Mapa de Navegación y Elementos de Interfaz", h2_style))
    story.append(Paragraph(
        "La interfaz del sistema utiliza un estilo visual <b>Glassmorphism</b> con fondos translúcidos y botones estandarizados:",
        body_style
    ))

    nav_buttons = [
        ["Control / Botón", "Color / Aspecto", "Función Operativa"],
        ["Iniciar Sesión / Entrar", "Verde brillante (#2ecc71)", "Valida las credenciales e ingresa al panel correspondiente."],
        ["Registrarse / Guardar", "Translúcido / Verde", "Registra un nuevo usuario en la base de datos comunitaria."],
        ["Registrar Depósito", "Acción Principal", "Abre el formulario de pesaje y selección de papelera inteligente."],
        ["Mapa SmartBins", "Acción Secundaria", "Despliega el plano de Cartagena con los marcadores de los 8 contenedores."],
        ["Retirar Puntos", "Acción Financiera", "Permite redimir eco-puntos por transferencias a líneas telefónicas móviles."],
        ["Historial Transacciones", "Informativo", "Consulta la bitácora contable de puntos acreditados y debitados."],
        ["Tabla Clasificación", "Competitivo", "Visualiza el ranking de los mejores recicladores (medallas oro/plata/bronce)."],
        ["Vaciar Papelera", "Rojo Advertencia", "Exclusivo de Administrador: restablece el nivel de carga del contenedor a 0.0 kg."]
    ]
    story.append(styled_table(nav_buttons, [120, 120, 300]))

    story.append(PageBreak())

    # 1.7 Descripción de Funciones Paso a Paso
    story.append(Paragraph("1.7 Descripción Detallada de Funciones del Sistema", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=8))

    # CU01 Registro
    story.append(Paragraph("1.7.1 Proceso de Registro de Ciudadano (Módulo de Afiliación)", h2_style))
    reg_steps = [
        ["Paso", "Acción del Usuario", "Comportamiento del Sistema"],
        ["Paso 1", "En la pantalla de bienvenida, dar clic en el botón 'Registrarse'.", "El sistema despliega el formulario en insertar_usuario.html."],
        ["Paso 2", "Digitar número de ID (documento), Nombre Completo, Correo y Usuario.", "El sistema valida que el correo no se encuentre previamente registrado."],
        ["Paso 3", "Oprimir el botón verde 'Guardar Usuario'.", "Inserta el usuario en la tabla 'usuario' y abre su monedero en 'cuenta_puntos' con saldo inicial 0."]
    ]
    story.append(styled_table(reg_steps, [50, 240, 250]))
    story.append(Spacer(1, 6))

    # CU02 Login
    story.append(Paragraph("1.7.2 Proceso de Inicio de Sesión y Control de Acceso", h2_style))
    login_steps = [
        ["Paso", "Acción del Usuario", "Comportamiento del Sistema"],
        ["Paso 1", "Seleccionar 'Iniciar Sesión' en la barra de navegación.", "Se presenta el formulario de acceso login.html."],
        ["Paso 2", "Ingresar el ID numérico o el Nombre de Usuario registrado.", "Se envía la petición POST a login.php."],
        ["Paso 3", "Hacer clic en 'Entrar al Panel'.", "Si el rol es 'admin', redirige a AdminPanel.php. Si es 'user', abre CuentaPuntos.php."]
    ]
    story.append(styled_table(login_steps, [50, 240, 250]))
    story.append(Spacer(1, 6))

    # CU03 Panel Dashboard
    story.append(Paragraph("1.7.3 Panel de Usuario (Dashboard Mi Cuenta)", h2_style))
    story.append(Paragraph(
        "Al ingresar con rol de usuario, se despliega el panel <code>CuentaPuntos.php</code> donde se aprecian:<br/>"
        "• <b>Insignia de Rango Ecológico:</b> <i>Novato</i> (0-99 pts), <i>Reciclador</i> (100-499 pts), "
        "<i>Eco-Guerrero</i> (500-999 pts) o <i>Maestro Eco</i> (1000+ pts).<br/>"
        "• <b>Saldo Actual de Eco-Puntos:</b> Actualizado dinámicamente con cada depósito.<br/>"
        "• <b>Métrica de Impacto:</b> Sumatoria total en kilogramos de residuos reciclados.<br/>"
        "• <b>Botones de Acceso Rápido:</b> Acceso directo a depósitos, mapas, retiros y clasificaciones.",
        body_style
    ))
    story.append(Spacer(1, 6))

    # CU04 Depósito de Residuos
    story.append(Paragraph("1.7.4 Registro de Depósito de Residuos y Pesaje", h2_style))
    dep_steps = [
        ["Paso", "Acción del Usuario", "Comportamiento del Sistema"],
        ["Paso 1", "Dar clic en el botón 'Registrar Depósito' en el menú principal.", "Despliega el formulario RegistrarResiduos.php."],
        ["Paso 2", "Seleccionar el tipo de residuo (Plástico, Vidrio, Electrónico, Papel/Cartón).", "Asocia el factor multiplicador de puntos."],
        ["Paso 3", "Ingresar el peso en kilogramos medido por la báscula (ej: 2.5).", "Prepara el cálculo matemático de puntos ganados."],
        ["Paso 4", "Elegir la papelera inteligente donde se deposita (ej. Bin #1 Hotel Casa Don Luis).", "Consulta la capacidad remanente del contenedor."],
        ["Paso 5", "Oprimir el botón 'Registrar Depósito'.", "Valida que el contenedor no esté lleno ni bloqueado. Acredita los puntos y actualiza el nivel."]
    ]
    story.append(styled_table(dep_steps, [50, 240, 250]))
    story.append(Spacer(1, 6))

    # CU05 Mapa de Bins
    story.append(Paragraph("1.7.5 Módulo de Mapa Interactivo y Ubicaciones de SmartBins", h2_style))
    story.append(Paragraph(
        "Permite geolocalizar en el Centro Histórico de Cartagena las 8 papeleras inteligentes activas. "
        "Los marcadores circulares cambian a color rojo si la papelera alcanza su límite de 50 kg.",
        body_style
    ))

    # Incluir imagen del mapa si existe
    map_img_path = r"C:\Users\Usuario\Desktop\Study\PRO-AULA\Smart_waste_bin_6to_semestre\mapa_cartagena.jpg"
    if os.path.exists(map_img_path):
        story.append(Spacer(1, 4))
        try:
            story.append(Image(map_img_path, width=420, height=260))
            story.append(Paragraph("<i>Figura 1.1: Plano cartográfico de ubicación de SmartBins en Cartagena de Indias.</i>", ParagraphStyle('Cap', parent=meta_style, alignment=1)))
        except Exception as e:
            pass
    story.append(Spacer(1, 6))

    # CU06 Canje de Puntos
    story.append(Paragraph("1.7.6 Canje y Retiro de Eco-Puntos", h2_style))
    cash_steps = [
        ["Paso", "Acción del Usuario", "Comportamiento del Sistema"],
        ["Paso 1", "Oprimir el botón 'Retirar Puntos' en el panel del usuario.", "Despliega la interfaz RetirarPuntos.php con equivalencia en $ COP."],
        ["Paso 2", "Digitar número de teléfono móvil de 10 dígitos (inicia en 3).", "Valida el formato de billetera virtual (Nequi / Daviplata)."],
        ["Paso 3", "Ingresar la cantidad de puntos a canjear.", "Verifica que los puntos solicitados sean menores o iguales al saldo disponible."],
        ["Paso 4", "Confirmar el retiro con el botón verde.", "Ejecuta una transacción ACID en MariaDB, descuenta el saldo y genera registro."]
    ]
    story.append(styled_table(cash_steps, [50, 240, 250]))

    story.append(PageBreak())

    # 1.8 Mesa de Ayuda
    story.append(Paragraph("1.8 Información sobre la Mesa de Ayuda y Soporte Técnico", h2_style))
    story.append(Paragraph(
        "Cuando el usuario experimente bloqueos de cuenta, fallos en la detección de pesaje o errores de validación, "
        "debe comunicarse con la mesa de ayuda oficial del proyecto:",
        body_style
    ))

    help_desk = [
        ["Canal de Atención", "Detalle de Contacto"],
        ["Líder Técnico de Soporte", "Klever Adriano Polo Bolívar — Administrador de Infraestructura"],
        ["Equipo de Desarrollo", "Juan Caro Ramírez, Diego Mejía Espinosa, Robert Pérez Maza"],
        ["Correo Electrónico de Mesa", "soporte.smartbin@unicolombo.edu.co"],
        ["Sede Institucional", "Fundación Universitaria Colombo Internacional — Sede Cuatro Vientos"],
        ["Horario de Atención", "Lunes a Viernes de 8:00 a.m. a 6:00 p.m. / Sábados de 8:00 a.m. a 12:00 m."],
        ["Herramienta de Gestión", "Sistema de Tickets y Auditoría de Base de Datos (MariaDB / GLPI)"]
    ]
    story.append(styled_table(help_desk, [160, 380]))
    story.append(Spacer(1, 10))

    # 1.9 Catálogo de Errores
    story.append(Paragraph("1.9 Descripción y Resolución de Mensajes de Error", h2_style))
    error_catalog = [
        ["Mensaje de Error en Pantalla", "Causa del Incidente", "Acción Correctiva para el Usuario"],
        ["'Error: Esta papelera está LLENA o BLOQUEADA'", "El contenedor ha alcanzado los 50.0 kg máximos de su volumen físico.", "Consulte el 'Mapa SmartBins' y deposite en otra estación disponible cercana. Espere el vaciado."],
        ["'Error: El peso ingresado excede la capacidad disponible'", "El residuo a depositar supera los kilogramos restantes para llegar a 50 kg.", "Divida el material en paquetes menores o deposítelo en un contenedor con mayor cupo."],
        ["'Error: Cantidad de puntos inválida o saldo insuficiente'", "El monto a retirar supera el balance actual o se digitó un número menor o igual a cero.", "Verifique su saldo en el dashboard y digite una cifra dentro de su disponibilidad."],
        ["'Error: El número de teléfono debe empezar por 3 y tener 10 dígitos'", "El número de móvil digitado no cumple el formato colombiano de telecomunicaciones.", "Verifique el número celular receptor (ej. 3151234567) e intente de nuevo."],
        ["'Error de conexión con base de datos'", "El servicio de base de datos MySQL/MariaDB se encuentra detenido.", "Notifique a la Mesa de Ayuda para reiniciar el servicio 'mysqld' en el servidor."]
    ]
    story.append(styled_table(error_catalog, [140, 180, 220]))

    story.append(PageBreak())

    # -------------------------------------------------------------------------
    # PARTE II: MANUAL TÉCNICO
    # -------------------------------------------------------------------------
    story.append(Paragraph("PARTE II: MANUAL TÉCNICO DE LA APLICACIÓN", h1_style))
    story.append(HRFlowable(width="100%", thickness=1.5, color=PRIMARY, spaceAfter=8))

    story.append(Paragraph(
        "El Manual Técnico suministra a los ingenieros de software, administradores de bases de datos y personal de soporte "
        "las especificaciones de diseño, modelos relacionales, diagramas UML, catálogo de código fuente y métricas de calidad "
        "necesarias para el mantenimiento y evolución del sistema Smart Waste Bin.",
        body_style
    ))

    # 2.1 Alcance
    story.append(Paragraph("2.1 Alcance del Manual Técnico", h2_style))
    story.append(Paragraph(
        "<b>Objetivo General:</b> Modelar, documentar e implementar la arquitectura del software de gestión de papeleras inteligentes, "
        "asegurando la persistencia transaccional de residuos pesados, la asignación algorítmica de eco-puntos y el monitoreo telemático.<br/>"
        "<b>Límites Técnicos:</b> Esta versión abarca la plataforma web (Stack LAMP/WAMP), los controladores PHP estructurados, las vistas "
        "responsivas y el motor MariaDB. La simulación de sensores se realiza mediante controladores de pesaje directo.",
        body_style
    ))
    story.append(Spacer(1, 6))

    # 2.2 Requerimientos y Reglas
    story.append(Paragraph("2.2 Requerimientos del Sistema y Reglas de Negocio", h2_style))
    rf_data = [
        ["Código", "Requerimiento Funcional (RF)", "Descripción Técnica"],
        ["RF01", "Registro de Ciudadanos", "Inserción atómica en 'usuario' y apertura de cuenta en 'cuenta_puntos' con saldo inicial 0."],
        ["RF02", "Control de Sesiones y Roles", "Autenticación basada en sesiones PHP nativas con segmentación de vistas para 'user' y 'admin'."],
        ["RF03", "Pesaje y Registro de Residuos", "Registro de material y peso en 'residuo', actualizando la columna 'nivel_actual' en 'smartbin'."],
        ["RF04", "Cálculo de Eco-Puntos", "Algoritmo: round(peso * factor_material). Factores: Plástico=10, Vidrio=15, Electrónico=50, Papel=5."],
        ["RF05", "Bloqueo Automático de Bin", "Cuando 'nivel_actual >= 50.0', el sistema cambia 'estado = Bloqueado' denegando depósitos."],
        ["RF06", "Telemetría Cartográfica", "Consulta dinámica a 'smartbin' para renderizar marcadores con porcentaje de carga en mapa visual."],
        ["RF07", "Canje y Transacción Financiera", "Transacción ACID que valida saldo, deduce puntos e inserta registro en 'transaccion_puntos'."],
        ["RF08", "Vaciado Operativo de Bins", "Controlador exclusivo de administrador para resetear 'nivel_actual = 0.0' y reactivar papelera."]
    ]
    story.append(styled_table(rf_data, [45, 165, 330]))
    story.append(Spacer(1, 8))

    # 2.3 Diagramas UML OMG 2.5.1
    story.append(Paragraph("2.3 Modelado de Arquitectura UML 2.5.1 (Draw.io)", h2_style))
    story.append(Paragraph(
        "El modelado formal del sistema Smart Waste Bin (SWB) comprende diez (10) modelos UML generados "
        "en Draw.io conforme a la especificación OMG UML 2.5.1, cubriendo estructura estática, comportamiento "
        "dinámico, interacción distribuida y topología de despliegue físico.",
        body_style
    ))

    diag_folder = r"C:\Users\Usuario\Desktop\Study\PRO-AULA\Smart_waste_bin_6to_semestre\diagramas"
    uml_list = [
        ("01-casos-de-uso.png", "2.3.1 Diagrama de Casos de Uso (UML 18)", "Actores Ciudadano, Admin y Sensor IoT con límites de subsistema y relaciones include/extend."),
        ("02-clases.png", "2.3.2 Diagrama de Clases del Dominio (UML 9, 11)", "Entidades de datos, composición con CuentaPuntos, agregación con TransaccionPuntos y enumeraciones."),
        ("03-secuencia.png", "2.3.3 Diagrama de Secuencia: Depósito y Liquidación (UML 17)", "Líneas de vida, validación de capacidad (50 kg) con fragmento alt y bloqueo preventivo."),
        ("04-actividades.png", "2.3.4 Diagrama de Actividades (UML 15)", "Flujo de trabajo procedimental con validación de peso (>0.05 kg) y control de desbordamiento."),
        ("05-estados.png", "2.3.5 Diagrama de Máquina de Estados (UML 14)", "Ciclo de vida físico del SmartBin: Disponible, Operativo, Alerta, Bloqueado y En Mantenimiento."),
        ("06-componentes.png", "2.3.6 Diagrama de Componentes (UML 11.6)", "Arquitectura en 3 capas: Cliente Android/Web, API REST PHP 8 con PDO y motor MariaDB InnoDB."),
        ("07-despliegue.png", "2.3.7 Diagrama de Arquitectura de Despliegue (UML 19)", "Topología física de nodos cúbicos (Smartphone, ESP32 IoT, Apache/PHP XAMPP y MariaDB)."),
        ("08-paquetes.png", "2.3.8 Diagrama de Paquetes y Modularización (UML 12)", "Estructura de namespaces com.unicolombo.smartwastebin y árbol modular backend."),
        ("09-comunicacion.png", "2.3.9 Diagrama de Comunicación (UML 17.9)", "Grafo de enlaces estáticos con numeración jerárquica de mensajes sincrónicos (1.0, 1.1, etc.)."),
        ("10-objetos.png", "2.3.10 Diagrama de Objetos - Instantánea (UML 9.8)", "Snapshot en tiempo de ejecución con instancias reales del usuario juanki y contenedor Hotel Casa Don Luis."),
        ("11-modelo-relacional.png", "2.3.11 Modelo Relacional (Perfil de Ambler)", "Esquema físico en MariaDB InnoDB: tablas, «PK», «FK», «UK», tipos de datos e integridad referencial."),
        ("12-der.png", "2.3.12 Modelo Entidad-Relación - DER (Notación de Chen)", "Modelo conceptual: entidades, relaciones (rombos), atributos (óvalos con PK subrayada) y participación total.")
    ]

    for png_name, d_title, d_desc in uml_list:
        p_img = os.path.join(diag_folder, png_name)
        if os.path.exists(p_img):
            story.append(Spacer(1, 4))
            story.append(Paragraph(d_title, h3_style))
            story.append(Paragraph(d_desc, body_style))
            try:
                with PILImage.open(p_img) as pim:
                    pw, ph = pim.size
                scale = min(500 / pw, 260 / ph)
                w_scaled = int(pw * scale)
                h_scaled = int(ph * scale)
                im_obj = Image(p_img, width=w_scaled, height=h_scaled)
                im_obj.hAlign = 'CENTER'
                story.append(im_obj)
                story.append(Spacer(1, 4))
            except Exception as e:
                pass
            story.append(PageBreak())

    story.append(PageBreak())

    # Diagrama de Clases
    story.append(Paragraph("2.3.4 Especificación de Clases del Dominio", h3_style))
    story.append(Paragraph(
        "A continuación se detallan las clases estructurales que definen el modelo de dominio del sistema:",
        body_style
    ))

    classes_spec = [
        ["Clase", "Atributos Principales", "Métodos y Responsabilidades"],
        ["Usuario", "• idUsuario: int\n• nombre: String\n• correo: String\n• usuario: String\n• rol: String\n• qrCode: String", "+ registrarse(): void\n+ iniciarSesion(): boolean\n+ consultarPuntos(): int\n+ editarPerfil(): boolean"],
        ["CuentaPuntos", "• idCuenta: int\n• idUsuario: int\n• saldoActual: int\n• fechaActualizacion: DateTime", "+ acumularPuntos(cantidad: int): void\n+ canjearPuntos(cantidad: int): boolean\n+ validarSaldo(puntos: int): boolean"],
        ["SmartBin", "• idBin: int\n• ubicacion: String\n• capacidadMaxima: float (50.0)\n• nivelActual: float\n• estado: String", "+ detectarResiduo(): void\n+ pesarResiduo(): float\n+ bloquearDeposito(): void\n+ vaciarContenedor(): void\n+ calcularPorcentaje(): float"],
        ["Residuo", "• idResiduo: int\n• idUsuario: int\n• idBin: int\n• tipo: String\n• peso: float\n• fecha: DateTime", "+ calcularPuntos(): int\n+ registrarEnBitacora(): boolean\n+ validarFraccion(): boolean"],
        ["TransaccionPuntos", "• idTransaccion: int\n• idCuenta: int\n• tipo: String\n• cantidad: int\n• fecha: DateTime", "+ registrarTransaccion(): boolean\n+ generarExtracto(): List"]
    ]
    story.append(styled_table(classes_spec, [90, 210, 240]))
    story.append(Spacer(1, 10))

    # 2.4 Administración de Usuarios
    story.append(Paragraph("2.4 Administración de Usuarios y Privilegios", h2_style))
    story.append(Paragraph(
        "El control de privilegios se gobierna en la tabla <code>usuario</code> mediante la columna <code>rol</code>:<br/>"
        "• <b>Perfil 'user':</b> Acceso a <code>CuentaPuntos.php</code>, <code>RegistrarResiduos.php</code>, "
        "<code>MapaSmartBins.php</code>, <code>RetirarPuntos.php</code> y <code>Transacciones.php</code>.<br/>"
        "• <b>Perfil 'admin':</b> Acceso exclusivo al portal de monitoreo <code>AdminPanel.php</code> con capacidad "
        "de ejecutar el vaciado operativo de contenedores saturados y depurar cuentas infractoras.",
        body_style
    ))
    story.append(Spacer(1, 8))

    # 2.5 Modelo Relacional y Diccionario
    story.append(Paragraph("2.5 Modelo Relacional y Diccionario de Datos (MariaDB)", h2_style))
    story.append(Paragraph(
        "La base de datos <b><code>bd_smartbin</code></b> implementa un esquema completamente normalizado en motor <b>InnoDB</b>, "
        "con integridad referencial y restricciones en cascada:",
        body_style
    ))

    # Diccionario usuario
    story.append(Paragraph("<b>Tabla: usuario (Entidad Ciudadano / Administrador)</b>", body_bold))
    dict_usr = [
        ["Columna", "Tipo SQL", "Nulo", "Clave", "Descripción y Reglas"],
        ["id_usuario", "INT(5)", "NO", "PK", "Identificador único del usuario (Cédula o código universitario)."],
        ["nombre", "VARCHAR(100)", "SÍ", "-", "Nombres y apellidos completos del ciudadano."],
        ["correo", "VARCHAR(100)", "SÍ", "UK", "Correo electrónico con restricción de unicidad."],
        ["usuario", "VARCHAR(50)", "SÍ", "-", "Nombre de usuario o alias para el inicio de sesión."],
        ["rol", "VARCHAR(20)", "SÍ", "-", "Nivel de acceso: 'user' o 'admin' (default: 'user')."],
        ["qr_code", "VARCHAR(255)", "SÍ", "-", "Cadena alfanumérica de validación QR en papelera física."],
        ["fecha_registro", "DATETIME", "SÍ", "-", "Estampa de tiempo del registro (CURRENT_TIMESTAMP)."]
    ]
    story.append(styled_table(dict_usr, [90, 80, 40, 45, 285]))
    story.append(Spacer(1, 6))

    # Diccionario cuenta_puntos
    story.append(Paragraph("<b>Tabla: cuenta_puntos (Monedero de Eco-Puntos)</b>", body_bold))
    dict_cp = [
        ["Columna", "Tipo SQL", "Nulo", "Clave", "Descripción y Reglas"],
        ["id_cuenta", "INT(11)", "NO", "PK-AI", "Identificador autoincremental de la cuenta de puntos."],
        ["id_usuario", "INT(5)", "SÍ", "FK-UK", "Clave foránea hacia usuario.id_usuario (ON DELETE CASCADE)."],
        ["saldo_actual", "INT(11)", "SÍ", "-", "Balance neto de eco-puntos disponibles (default: 0)."],
        ["fecha_actualizacion", "DATETIME", "SÍ", "-", "Fecha y hora del último movimiento del monedero."]
    ]
    story.append(styled_table(dict_cp, [90, 80, 40, 45, 285]))
    story.append(Spacer(1, 6))

    # Diccionario smartbin
    story.append(Paragraph("<b>Tabla: smartbin (Contenedores Físicos Georreferenciados)</b>", body_bold))
    dict_sb = [
        ["Columna", "Tipo SQL", "Nulo", "Clave", "Descripción y Reglas"],
        ["id_bin", "INT(11)", "NO", "PK-AI", "Identificador autoincremental del contenedor inteligente."],
        ["ubicacion", "VARCHAR(150)", "SÍ", "-", "Ubicación geográfica o nombre del hito (ej: 'Hotel Casa Don Luis')."],
        ["capacidad_maxima", "FLOAT", "SÍ", "-", "Capacidad límite en kilogramos (default: 50.0)."],
        ["nivel_actual", "FLOAT", "SÍ", "-", "Carga actual en kilogramos registrada por sensores (default: 0.0)."],
        ["estado", "VARCHAR(50)", "SÍ", "-", "Disponibilidad del contenedor: 'Activo' o 'Bloqueado'."]
    ]
    story.append(styled_table(dict_sb, [90, 80, 40, 45, 285]))

    story.append(PageBreak())

    # Diccionario residuo y transaccion_puntos
    story.append(Paragraph("<b>Tabla: residuo (Historial de Pesaje y Depósitos)</b>", body_bold))
    dict_res = [
        ["Columna", "Tipo SQL", "Nulo", "Clave", "Descripción y Reglas"],
        ["id_residuo", "INT(11)", "NO", "PK-AI", "Identificador único autoincremental del residuo depositado."],
        ["id_usuario", "INT(5)", "SÍ", "FK", "Usuario que realizó el depósito (FK hacia usuario.id_usuario)."],
        ["id_bin", "INT(11)", "SÍ", "FK", "Papelera receptora del material (FK hacia smartbin.id_bin)."],
        ["tipo", "VARCHAR(50)", "SÍ", "-", "Fracción ecológica: 'PLASTICO', 'ORGANICO', 'ELECTRONICO', etc."],
        ["peso", "FLOAT", "SÍ", "-", "Peso exacto registrado en kilogramos por la celda de carga."],
        ["fecha", "DATETIME", "SÍ", "-", "Estampa de tiempo del depósito (CURRENT_TIMESTAMP)."]
    ]
    story.append(styled_table(dict_res, [90, 80, 40, 45, 285]))
    story.append(Spacer(1, 6))

    story.append(Paragraph("<b>Tabla: transaccion_puntos (Libro Mayor de Movimientos)</b>", body_bold))
    dict_tp = [
        ["Columna", "Tipo SQL", "Nulo", "Clave", "Descripción y Reglas"],
        ["id_transaccion", "INT(11)", "NO", "PK-AI", "Identificador único autoincremental de la transacción contable."],
        ["id_cuenta", "INT(11)", "SÍ", "FK", "Cuenta de puntos afectada (FK hacia cuenta_puntos.id_cuenta)."],
        ["tipo", "VARCHAR(50)", "SÍ", "-", "Concepto transaccional: 'DEPOSITO_PLASTICO', 'RETIRO_TEL_315...', etc."],
        ["cantidad", "INT(11)", "SÍ", "-", "Monto entero de eco-puntos acreditados o debitados."],
        ["fecha", "DATETIME", "SÍ", "-", "Estampa de tiempo de la transacción contable."]
    ]
    story.append(styled_table(dict_tp, [90, 80, 40, 45, 285]))
    story.append(Spacer(1, 10))

    # 2.6 Descripción de la Plataforma
    story.append(Paragraph("2.6 Descripción de la Plataforma e Infraestructura", h2_style))
    infra_table = [
        ["Capa Tecnológica", "Herramienta Seleccionada", "Versión Mínima", "Función en el Ecosistema"],
        ["Sistema Operativo", "Windows 10/11 / Ubuntu Server", "64-bit", "Hospedaje del servidor web y servicios de backend."],
        ["Servidor Web", "Apache HTTP Server", "2.4.58+", "Atención de peticiones HTTP/HTTPS, enrutamiento y MIME types."],
        ["Motor de Base de Datos", "MariaDB / MySQL Server", "10.4.32+", "Persistencia relacional, motor InnoDB y transacciones ACID."],
        ["Lenguaje Backend", "PHP (Hypertext Preprocessor)", "8.2.12+", "Lógica de negocio, controladores, cálculo algorítmico y sesiones."],
        ["Frontend UI", "HTML5 + CSS3 + Vanilla JS", "Estándar W3C", "Vistas responsivas Glassmorphism con fondo follaje institucional."],
        ["Controlador de Pila", "XAMPP Control Panel", "v3.3.0+", "Orquestador de servicios locales Apache y MariaDB."]
    ]
    story.append(styled_table(infra_table, [100, 130, 80, 230]))
    story.append(Spacer(1, 10))

    # 2.7 Código Fuente
    story.append(Paragraph("2.7 Documentación del Código Fuente y Estructura de Directorios", h2_style))
    code_cat = [
        ["Archivo / Programa", "Rol", "Entradas (Inputs)", "Salidas (Outputs)", "Descripción Técnica"],
        ["db.php", "Conexión", "Credenciales BD", "$conn (mysqli)", "Conexión persistente a la base bd_smartbin."],
        ["login.php", "Controlador", "POST['usuario']", "Sesión / Redirect", "Verifica credenciales, inicia sesión y bifurca por rol."],
        ["insertar_usuario.php", "Controlador", "id, nombre, correo", "Registro BD", "Inserta usuario y crea monedero inicial con 0 puntos."],
        ["CuentaPuntos.php", "Controlador", "$_SESSION['user_id']", "Variables Vista", "Calcula puntos, total kg y determina rango ecológico."],
        ["procesar_deposito.php", "Controlador", "tipo, peso, id_bin", "Transacciones SQL", "Aplica factores, valida holgura de 50 kg y abona puntos."],
        ["MapaSmartBins.php", "Controlador", "Query a smartbin", "$smartbins array", "Carga estado y nivel de los 8 contenedores de Cartagena."],
        ["procesar_retiro.php", "Controlador", "telefono, cantidad", "Transacción ACID", "Valida celular (10 dígitos), descuenta saldo y registra retiro."],
        ["vaciar_bin.php", "Admin", "GET['id']", "Update smartbin", "Restablece nivel_actual a 0.0 kg y reactiva contenedor."]
    ]
    story.append(styled_table(code_cat, [105, 55, 95, 95, 190]))

    story.append(PageBreak())

    # 2.8 ANS / SLA
    story.append(Paragraph("2.8 Acuerdos de Niveles de Servicio (ANS / SLA) y Matriz de Escalamiento", h2_style))
    story.append(Paragraph(
        "Define los compromisos de disponibilidad, tiempos de respuesta y rutas de resolución técnica ante anomalías:",
        body_style
    ))

    sla_data = [
        ["Severidad", "Tipología del Incidente", "Tiempo Respuesta", "Tiempo Solución", "Responsable"],
        ["Nivel 1 (Crítico)", "Caída de MariaDB o Apache; imposibilidad total de registrar depósitos urbanos.", "< 30 min", "< 2 horas", "Klever Polo (Admin BD)"],
        ["Nivel 2 (Alto)", "Contenedor inteligente bloqueado al 100% de capacidad en zona turística.", "< 1 hora", "< 4 horas", "Cuadrilla de Aseo y Vaciado"],
        ["Nivel 3 (Medio)", "Retraso en la visualización de saldo de eco-puntos tras depósito validado.", "< 4 horas", "< 12 horas", "Juan Caro / Robert Pérez"],
        ["Nivel 4 (Bajo)", "Consultas de usuario, olvido de ID numérico o solicitudes de cambio de alias.", "< 8 horas", "< 24 horas", "Diego Mejía (Mesa de Ayuda)"]
    ]
    story.append(styled_table(sla_data, [80, 170, 75, 75, 140]))
    story.append(Spacer(1, 14))

    # 3. Glosario
    story.append(Paragraph("Glosario de Términos", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=8))
    glossary_data = [
        ["Término", "Definición Conceptual y Técnica"],
        ["ANS (Acuerdo de Niveles de Servicio)", "Compromiso documentado que estipula tiempos máximos de respuesta y mitigación de fallos de software."],
        ["Glassmorphism", "Técnica de diseño de interfaz de usuario caracterizada por translucidez, bordes iluminados y efecto de desenfoque de fondo."],
        ["IoT (Internet of Things)", "Ecosistema de dispositivos físicos equipados con sensores y actuadores interconectados a redes telemáticas."],
        ["SmartBin", "Contenedor inteligente con instrumentación para medir peso, nivel de llenado y reportar telemetría a un servidor central."],
        ["Transacción ACID", "Mecanismo transaccional que garantiza Atomicidad, Consistencia, Aislamiento y Durabilidad en operaciones de base de datos."]
    ]
    story.append(styled_table(glossary_data, [130, 410]))
    story.append(Spacer(1, 14))

    # 4. Bibliografía
    story.append(Paragraph("Referencias Bibliográficas", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=8))
    story.append(Paragraph("1. <b>IEEE Computer Society.</b> (2001). <i>IEEE Std 1063-2001: IEEE Standard for Software User Documentation</i>. IEEE Standards Association. Piscataway, NJ, USA.", body_style))
    story.append(Paragraph("2. <b>Servicio Nacional de Aprendizaje (SENA).</b> (2020). <i>Manuales Técnicos de la Aplicación</i>. Centro Industrial de Mantenimiento Integral (CIMI) - Regional Santander. Formación en Ambientes Virtuales de Aprendizaje (FAVA).", body_style))
    story.append(Paragraph("3. <b>Pressman, R. S., & Maxim, B. R.</b> (2020). <i>Software Engineering: A Practitioner's Approach</i> (9th ed.). McGraw-Hill Education.", body_style))
    story.append(Paragraph("4. <b>Sommerville, I.</b> (2019). <i>Ingeniería del Software</i> (10.ª ed.). Pearson Educación.", body_style))
    story.append(Paragraph("5. <b>The PHP Group.</b> (2026). <i>PHP Manual: MySQL Improved Extension (mysqli)</i>. Recuperado de https://www.php.net/manual/es/book.mysqli.php", body_style))
    story.append(Spacer(1, 14))

    # 5. Control del Documento
    story.append(Paragraph("Control del Documento y Registro de Firmas", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=8))

    doc_control = [
        ["Rol en el Proyecto", "Nombre y Apellidos", "Institución / Programa", "Firma / Aprobación"],
        ["Líder Técnico y Base de Datos", "Klever Adriano Polo Bolívar", "UNICOLOMBO - Ingeniería de Sistemas", "Aprobado"],
        ["Desarrollador de Lógica y Módulos", "Juan Caro Ramírez", "UNICOLOMBO - Ingeniería de Sistemas", "Aprobado"],
        ["Analista de Requerimientos y Casos", "Diego Mejía Espinosa", "UNICOLOMBO - Ingeniería de Sistemas", "Aprobado"],
        ["Diseñador de Datos y Dominio", "Robert Pérez Maza", "UNICOLOMBO - Ingeniería de Sistemas", "Aprobado"],
        ["Docente Director de Cátedra", "Ing. Juan José Puello Beltrán", "Docente Titular de Cátedra", "Visto Bueno"]
    ]
    story.append(styled_table(doc_control, [130, 130, 180, 100]))

    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"PDF generado exitosamente en: {filename}")

if __name__ == "__main__":
    out_path = sys.argv[1] if len(sys.argv) > 1 else "MANUAL_TECNICO_Y_DE_USUARIO_SMART_WASTE_BIN.pdf"
    build_pdf(out_path)
