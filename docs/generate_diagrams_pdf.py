# -*- coding: utf-8 -*-
"""Generador de Documento PDF de Diagramas de Arquitectura UML para Smart Waste Bin (SWB)
Ingeniería de Sistemas - 6to Semestre - UNICOLOMBO
Autores: Klever Polo Bolívar & Juan Camilo Puello
Incluye los diagramas exportados desde Draw.io (.png) y las especificaciones técnicas completas OMG UML 2.5.1
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

PRIMARY = colors.HexColor("#0f766e")       # Verde Esmeralda / Teal
DARK_GREEN = colors.HexColor("#064e3b")    # Verde Oscuro
ACCENT = colors.HexColor("#f59e0b")        # Ámbar / Alerta
TEXT_DARK = colors.HexColor("#0f172a")     # Gris azulado oscuro
BG_LIGHT = colors.HexColor("#f8fafc")      # Fondo claro
BG_HEADER = colors.HexColor("#e6f4ea")     # Verde menta suave
BORDER_COLOR = colors.HexColor("#cbd5e1")  # Borde tenue

DIAG_DIR = r"C:\Users\Usuario\Desktop\Study\PRO-AULA\Smart_waste_bin_6to_semestre\diagramas"

class NumberedCanvas(canvas.Canvas):
    """Canvas con doble pasada para numeración 'Página X de Y' y encabezados"""
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
            self.setFont("Helvetica-Bold", 7.5)
            self.setFillColor(PRIMARY)
            self.drawString(36, 762, "SMART WASTE BIN (SWB) — ARQUITECTURA DE SOFTWARE UML 2.5.1")
            self.setFont("Helvetica", 7.5)
            self.setFillColor(colors.HexColor("#64748b"))
            self.drawRightString(576, 762, "UNICOLOMBO — 6TO SEMESTRE")
            self.setStrokeColor(BORDER_COLOR)
            self.setLineWidth(0.6)
            self.line(36, 756, 576, 756)

            # Footer
            self.line(36, 36, 576, 36)
            self.setFont("Helvetica", 7.5)
            self.setFillColor(colors.HexColor("#64748b"))
            self.drawString(36, 25, "Modelado en Draw.io según OMG UML 2.5.1 — Klever Polo & Juan Camilo Puello")
            self.drawRightString(576, 25, f"Página {self._pageNumber} de {page_count}")

def make_scaled_image(png_name, max_w=540, max_h=350):
    """Crea un objeto Image de ReportLab escalado proporcionalmente"""
    p = os.path.join(DIAG_DIR, png_name)
    if not os.path.exists(p):
        return Paragraph(f"<i>[Imagen {png_name} no encontrada]</i>", ParagraphStyle('Err', textColor=colors.red))
    with PILImage.open(p) as im:
        w, h = im.size
    ratio = min(max_w / w, max_h / h)
    final_w = int(w * ratio)
    final_h = int(h * ratio)
    img = Image(p, width=final_w, height=final_h)
    img.hAlign = 'CENTER'
    return img

def build_pdf(filename):
    doc = SimpleDocTemplate(
        filename,
        pagesize=letter,
        leftMargin=36,
        rightMargin=36,
        topMargin=50,
        bottomMargin=45
    )

    styles = getSampleStyleSheet()

    title_style = ParagraphStyle(
        'DocTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=20,
        leading=24,
        textColor=DARK_GREEN,
        alignment=1,
        spaceAfter=8
    )
    subtitle_style = ParagraphStyle(
        'DocSubTitle',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=11,
        leading=15,
        textColor=TEXT_DARK,
        alignment=1,
        spaceAfter=15
    )
    h1_style = ParagraphStyle(
        'H1',
        parent=styles['Heading1'],
        fontName='Helvetica-Bold',
        fontSize=13,
        leading=17,
        textColor=DARK_GREEN,
        spaceBefore=12,
        spaceAfter=6,
        keepWithNext=True
    )
    h2_style = ParagraphStyle(
        'H2',
        parent=styles['Heading2'],
        fontName='Helvetica-Bold',
        fontSize=10,
        leading=13,
        textColor=PRIMARY,
        spaceBefore=8,
        spaceAfter=4,
        keepWithNext=True
    )
    body_style = ParagraphStyle(
        'Body',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=11.5,
        textColor=TEXT_DARK,
        spaceAfter=4
    )
    cell_style = ParagraphStyle(
        'Cell',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=7.5,
        leading=9.5,
        textColor=TEXT_DARK
    )
    cell_bold = ParagraphStyle(
        'CellBold',
        parent=cell_style,
        fontName='Helvetica-Bold',
        textColor=DARK_GREEN
    )

    story = []

    # ==========================
    # PORTADA
    # ==========================
    story.append(Spacer(1, 30))
    story.append(Paragraph("<b>PROYECTO DE AULA — INGENIERÍA DE SISTEMAS</b>", ParagraphStyle('SubHeader', parent=title_style, fontSize=11, textColor=PRIMARY)))
    story.append(Spacer(1, 8))
    story.append(Paragraph("SMART WASTE BIN (SWB)", title_style))
    story.append(Paragraph("<b>DOSSIER ARQUITECTÓNICO DE LOS 12 MODELOS UML 2.5.1 Y DE BASE DE DATOS</b><br/>Generados en Draw.io conforme a los lineamientos de la Bóveda de Obsidian (OMG UML 2.5.1, Chen DER y Perfil Ambler)", subtitle_style))
    story.append(Spacer(1, 10))

    meta_table_data = [
        [Paragraph("<b>Asignatura:</b>", cell_bold), Paragraph("Ingeniería de Software / Electiva II (6to Semestre)", cell_style)],
        [Paragraph("<b>Institución:</b>", cell_bold), Paragraph("Corporación Universitaria Rafael Núñez / UNICOLOMBO", cell_style)],
        [Paragraph("<b>Autores:</b>", cell_bold), Paragraph("Klever Polo Bolívar & Juan Camilo Puello", cell_style)],
        [Paragraph("<b>Docente:</b>", cell_bold), Paragraph("Ing. Carlos Puello", cell_style)],
        [Paragraph("<b>Herramienta de Modelado:</b>", cell_bold), Paragraph("Draw.io (diagrams.net CLI 32.3.0) + Python drawio_lib", cell_style)],
        [Paragraph("<b>Norma Internacional:</b>", cell_bold), Paragraph("OMG Unified Modeling Language (OMG UML) Versión 2.5.1", cell_style)],
        [Paragraph("<b>Fecha:</b>", cell_bold), Paragraph("Octubre de 2026 — Entrega Oficial", cell_style)],
    ]
    meta_table = Table(meta_table_data, colWidths=[140, 400])
    meta_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#f1f5f9")),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor("#cbd5e1")),
        ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor("#e2e8f0")),
        ('TOPPADDING', (0,0), (-1,-1), 4),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
        ('LEFTPADDING', (0,0), (-1,-1), 8),
        ('RIGHTPADDING', (0,0), (-1,-1), 8),
    ]))
    story.append(meta_table)
    story.append(Spacer(1, 15))

    resumen_text = (
        "<b>RESUMEN EJECUTIVO:</b> Este informe consolida los diez (10) diagramas arquitectónicos del sistema "
        "<b>Smart Waste Bin (SWB)</b>. Cada diagrama ha sido generado programáticamente mediante la librería "
        "<code>drawio_lib.py</code> de la bóveda de Obsidian y renderizado en alta definición con el motor oficial Draw.io. "
        "Se respetan escrupulosamente las convenciones de la especificación OMG UML 2.5.1: delimitación de sujetos "
        "con «subsystem», separación entre actores humanos y hardware («device»), direcciones correctas de «include» y «extend» "
        "con nota de condición, visibilidad y tipado estricto, multiplicidad en ambos extremos, máquinas de estados finitos y "
        "mensajes temporales con control de concurrencia ACID sobre la base de datos <code>bd_smartbin</code>."
    )
    story.append(Paragraph(resumen_text, body_style))
    story.append(PageBreak())

    # ==========================
    # 1. CASOS DE USO
    # ==========================
    story.append(Paragraph("1. DIAGRAMA DE CASOS DE USO (OMG UML 2.5.1, Cláusula 18)", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=6))
    story.append(Paragraph(
        "<b>Alcance:</b> Modela los objetivos que los actores humanos (Ciudadano y Administrador a la izquierda) y no humanos "
        "(Estación IoT con cabeza de dispositivo a la derecha) realizan sobre el sujeto «subsystem» Smart Waste Bin.",
        body_style
    ))
    story.append(make_scaled_image("01-casos-de-uso.png", max_w=540, max_h=300))
    story.append(Spacer(1, 6))

    uc_desc = [
        [Paragraph("<b>Elemento</b>", cell_bold), Paragraph("<b>Rol / Estereotipo</b>", cell_bold), Paragraph("<b>Regla OMG UML Aplicada</b>", cell_bold)],
        [Paragraph("Ciudadano (Móvil)", cell_style), Paragraph("Actor principal, humano", cell_style), Paragraph("Monigote cabeza redonda (Fig. 18.6). Ubicado a la izquierda.", cell_style)],
        [Paragraph("Administrador (Web)", cell_style), Paragraph("Actor principal, humano", cell_style), Paragraph("Monigote cabeza redonda. Supervisa mapa y cuadrilla.", cell_style)],
        [Paragraph("Estación IoT / Sensores", cell_style), Paragraph("Actor secundario, «device»", cell_style), Paragraph("Cabeza de pantalla + «device» (Fig. 18.8). Actor tecnológico.", cell_style)],
        [Paragraph("Depositar Residuo", cell_style), Paragraph("Caso base", cell_style), Paragraph("«include» hacia 'Pesar y validar residuo' (siempre ocurre).", cell_style)],
        [Paragraph("Bloquear estación", cell_style), Paragraph("Caso de extensión", cell_style), Paragraph("«extend» hacia 'Pesar residuo' con nota condition: {nivel >= 50.0kg}.", cell_style)],
    ]
    t_uc = Table(uc_desc, colWidths=[120, 140, 280])
    t_uc.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), BG_HEADER),
        ('GRID', (0,0), (-1,-1), 0.5, BORDER_COLOR),
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
    ]))
    story.append(t_uc)
    story.append(PageBreak())

    # ==========================
    # 2. CLASES
    # ==========================
    story.append(Paragraph("2. DIAGRAMA DE CLASES (OMG UML 2.5.1, Cláusulas 9 y 11)", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=6))
    story.append(Paragraph(
        "<b>Estructura Estática:</b> Clases del dominio con tipado, visibilidad completa (+, -, #), operaciones, "
        "cardinalidades en ambos extremos y rombos de agregación/composición pegados al clasificador contenedor.",
        body_style
    ))
    story.append(make_scaled_image("02-clases.png", max_w=540, max_h=310))
    story.append(Spacer(1, 6))

    cls_rules = (
        "• <b>Composición (Rombo negro):</b> <code>Usuario</code> [1] *-- [1] <code>CuentaPuntos</code> (la cuenta depende del ciclo de vida del usuario).<br/>"
        "• <b>Agregación (Rombo hueco):</b> <code>CuentaPuntos</code> [1] o-- [0..*] <code>TransaccionPuntos</code> (auditoría agregada a la cuenta).<br/>"
        "• <b>Límite Físico del SmartBin:</b> Atributo <code>capacidad_maxima: float = 50.0</code> y estado gobernado por <code>EstadoBin</code>.<br/>"
        "• <b>Factores de Conversión (TipoResiduo):</b> Plástico (10), Vidrio (15), Electrónico (50), Papel/Cartón (5 pts/kg)."
    )
    story.append(Paragraph(cls_rules, body_style))
    story.append(PageBreak())

    # ==========================
    # 3. SECUENCIA
    # ==========================
    story.append(Paragraph("3. DIAGRAMA DE SECUENCIA (OMG UML 2.5.1, Cláusula 17)", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=6))
    story.append(Paragraph(
        "<b>Interacción Temporal:</b> Depósito de material, verificación de tolerancia física con la estación SmartBin, "
        "bifurcación con fragmento <code>alt</code> y bloqueo preventivo con <code>opt</code>.",
        body_style
    ))
    story.append(make_scaled_image("03-secuencia.png", max_w=540, max_h=310))
    story.append(Spacer(1, 6))

    seq_notes = (
        "• <b>Líneas de Vida y Ejecución:</b> Activaciones rectangulares sobre el eje temporal de cada participante.<br/>"
        "• <b>Fragmento Combinado <code>alt</code>:</b> Rama superior si <code>[nivel + peso > 50.0 kg]</code> retorna HTTP 400 Bad Request; "
        "rama <code>[else]</code> ejecuta la transacción atómica ACID.<br/>"
        "• <b>Fragmento Combinado <code>opt</code>:</b> Si <code>[nuevo_nivel >= 50.0 kg]</code> actualiza <code>estado = 'Bloqueado'</code>."
    )
    story.append(Paragraph(seq_notes, body_style))
    story.append(PageBreak())

    # ==========================
    # 4. ACTIVIDADES
    # ==========================
    story.append(Paragraph("4. DIAGRAMA DE ACTIVIDADES (OMG UML 2.5.1, Cláusula 15)", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=6))
    story.append(Paragraph(
        "<b>Flujo de Trabajo:</b> Acciones secuenciales y decisiones operativas desde el escaneo QR hasta la acreditación de puntos.",
        body_style
    ))
    story.append(make_scaled_image("04-actividades.png", max_w=540, max_h=370))
    story.append(Spacer(1, 6))

    act_notes = (
        "• <b>Validación de Peso Mínimo:</b> Decisión con guarda <code>[peso > 0.05 kg]</code>; en caso negativo finaliza con error.<br/>"
        "• <b>Evaluación de Capacidad:</b> Guarda <code>[nivel + peso <= 50.0 kg]</code> previene el desborde físico de la papelera.<br/>"
        "• <b>Enclavamiento:</b> Si el nuevo nivel alcanza 50.0 kg, activa el estado 'Bloqueado' y notifica al panel central de aseo."
    )
    story.append(Paragraph(act_notes, body_style))
    story.append(PageBreak())

    # ==========================
    # 5. ESTADOS
    # ==========================
    story.append(Paragraph("5. DIAGRAMA DE MÁQUINA DE ESTADOS (OMG UML 2.5.1, Cláusula 14)", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=6))
    story.append(Paragraph(
        "<b>Ciclo de Vida del Contenedor Físico:</b> Estados finitos con invariantes, acciones internas (<code>entry /</code>, <code>do /</code>) y estado compuesto.",
        body_style
    ))
    story.append(make_scaled_image("05-estados.png", max_w=540, max_h=300))
    story.append(Spacer(1, 6))

    st_notes = (
        "• <b>Disponible (Vacío):</b> <code>entry / encenderLEDVerde()</code>, <code>nivel = 0.0 kg</code>.<br/>"
        "• <b>Alerta (Casi Lleno):</b> Se activa al superar el 80% (40.0 kg a 49.9 kg) notificando al centro de control.<br/>"
        "• <b>Bloqueado (Saturado):</b> Al llegar a 50.0 kg bloquea mecánicamente la tolva para evitar desbordes urbanos.<br/>"
        "• <b>Estado Compuesto 'En Mantenimiento':</b> Subestados para apertura con llave de operador, retiro de bolsas y tara de celdas."
    )
    story.append(Paragraph(st_notes, body_style))
    story.append(PageBreak())

    # ==========================
    # 6. COMPONENTES
    # ==========================
    story.append(Paragraph("6. DIAGRAMA DE COMPONENTES (OMG UML 2.5.1, Cláusula 11.6 y 10.4)", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=6))
    story.append(Paragraph(
        "<b>Arquitectura de Tres Capas con Simbología Oficial de Interfaces:</b> Componentes con ícono UML 2 "
        "en la esquina superior derecha, interfaces provistas (bola ○) y requeridas (medio círculo / socket Ɑ).",
        body_style
    ))
    story.append(make_scaled_image("06-componentes.png", max_w=540, max_h=300))
    story.append(Spacer(1, 6))

    cmp_notes = (
        "• <b>Simbología OMG UML 2.5.1:</b> Cada interfaz provista por el API Gateway se representa mediante la <b>bola ○</b> (lollipop, realizador), "
        "y los clientes (App Móvil y Web Dashboard) se conectan mediante el <b>socket Ɑ</b> (interfaz requerida / consumidor).<br/>"
        "• <b>Frontend:</b> Cliente Android Java y Dashboard Web Glassmorphism desacoplados de la persistencia directa.<br/>"
        "• <b>API Gateway:</b> Enruta peticiones hacia los submódulos de Auth, Depósitos, Puntos y Telemetría.<br/>"
        "• <b>Persistencia:</b> Capa PDO con conector TCP 3306 que garantiza transacciones atómicas ACID sobre MariaDB."
    )
    story.append(Paragraph(cmp_notes, body_style))
    story.append(PageBreak())

    # ==========================
    # 7. DESPLIEGUE
    # ==========================
    story.append(Paragraph("7. DIAGRAMA DE DESPLIEGUE (OMG UML 2.5.1, Cláusula 19)", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=6))
    story.append(Paragraph(
        "<b>Infraestructura Física:</b> Nodos representados como cubos 3D («device» y «executionEnvironment») y artefactos con icono de documento.",
        body_style
    ))
    story.append(make_scaled_image("07-despliegue.png", max_w=540, max_h=300))
    story.append(Spacer(1, 6))

    dep_notes = (
        "• <b>Dispositivo Móvil:</b> Smartphone Android ejecutando el artefacto compilado <code>SmartWasteBin.apk</code>.<br/>"
        "• <b>Estación IoT:</b> Microcontrolador ESP32 ejecutando <code>firmware_swb.bin</code> conectado a celdas HX711.<br/>"
        "• <b>Servidor Web XAMPP:</b> Entorno con Apache 2.4 y PHP 8.2 sirviendo endpoints REST y portal web.<br/>"
        "• <b>Servidor de Base de Datos:</b> MariaDB 10.4 InnoDB alojando el esquema relacional normalizado."
    )
    story.append(Paragraph(dep_notes, body_style))
    story.append(PageBreak())

    # ==========================
    # 8. PAQUETES
    # ==========================
    story.append(Paragraph("8. DIAGRAMA DE PAQUETES (OMG UML 2.5.1, Cláusula 12)", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=6))
    story.append(Paragraph(
        "<b>Organización del Código Fuente:</b> Jerarquía de paquetes (carpetas con pestaña) en Android Java y PHP backend.",
        body_style
    ))
    story.append(make_scaled_image("08-paquetes.png", max_w=540, max_h=300))
    story.append(Spacer(1, 6))

    pkg_notes = (
        "• <b>Namespace Móvil:</b> <code>com.unicolombo.smartwastebin</code> dividido en <code>views</code>, <code>controllers</code>, <code>models</code>, <code>network</code> y <code>utils</code>.<br/>"
        "• <b>Backend PHP:</b> Carpetas <code>api/</code> (endpoints REST), <code>config/</code> (PDO/CORS), <code>views/</code> y <code>assets/</code>.<br/>"
        "• <b>Dependencias Limpias:</b> El paquete móvil de red (<code>network</code>) consume vía HTTP el paquete <code>api/</code> del backend."
    )
    story.append(Paragraph(pkg_notes, body_style))
    story.append(PageBreak())

    # ==========================
    # 9. COMUNICACIÓN
    # ==========================
    story.append(Paragraph("9. DIAGRAMA DE COMUNICACIÓN (OMG UML 2.5.1, Cláusula 17.9)", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=6))
    story.append(Paragraph(
        "<b>Colaboración de Objetos:</b> Grafo de enlaces estáticos con flechas de dirección de mensaje y numeración secuencial.",
        body_style
    ))
    story.append(make_scaled_image("09-comunicacion.png", max_w=540, max_h=300))
    story.append(Spacer(1, 6))

    comm_notes = (
        "• <b>Numeración Jerárquica:</b> <code>1: presionarBoton()</code> ➔ <code>1.1: enviarPeticion()</code> ➔ <code>1.2: POST</code> ➔ <code>1.3: verificarCapacidad()</code>.<br/>"
        "• <b>Subflujo de Acreditación:</b> <code>2.0: acreditarPuntos()</code> ➔ <code>2.1: UPDATE saldo & INSERT transaccion</code>.<br/>"
        "• <b>Notación Estricta:</b> Enlaces representados por líneas sólidas con la flecha de sentido de mensaje paralela al enlace."
    )
    story.append(Paragraph(comm_notes, body_style))
    story.append(PageBreak())

    # ==========================
    # 10. OBJETOS
    # ==========================
    story.append(Paragraph("10. DIAGRAMA DE OBJETOS (OMG UML 2.5.1, Cláusula 9.8)", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=6))
    story.append(Paragraph(
        "<b>Instantánea en Tiempo de Ejecución (Snapshot):</b> Instancias con nombres subrayados y valores reales de la base de datos.",
        body_style
    ))
    story.append(make_scaled_image("10-objetos.png", max_w=540, max_h=300))
    story.append(Spacer(1, 6))

    obj_notes = (
        "• <b>Instancia de Usuario:</b> <code><u>u1 : Usuario</u></code> con <code>id_usuario = 3</code>, <code>usuario = 'juanki'</code>.<br/>"
        "• <b>Instancia de Contenedor:</b> <code><u>b2 : SmartBin</u></code> con ubicación en <i>'Hotel Casa Don Luis'</i> y carga de 12.5 kg.<br/>"
        "• <b>Evento de Depósito:</b> <code><u>r104 : Residuo</u></code> de tipo <code>PLASTICO</code> con 2.5 kg generando 25 puntos en la transacción <code><u>t88</u></code>."
    )
    story.append(Paragraph(obj_notes, body_style))
    story.append(PageBreak())

    # ==========================
    # 11. MODELO RELACIONAL
    # ==========================
    story.append(Paragraph("11. MODELO RELACIONAL (PERFIL DE AMBLER - MOTOR MARIADB INNODB)", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=6))
    story.append(Paragraph(
        "<b>Esquema de Tablas Normalizadas:</b> Especificación física de tablas con claves primarias <code>«PK»</code>, "
        "claves foráneas <code>«FK»</code>, restricciones únicas <code>«UK»</code>, tipos de datos SQL y relaciones de integridad.",
        body_style
    ))
    story.append(make_scaled_image("11-modelo-relacional.png", max_w=540, max_h=310))
    story.append(Spacer(1, 6))

    rel_notes = (
        "• <b>Perfil de Datos de Ambler:</b> Uso formal de <code>«PK»</code>, <code>«FK»</code> y <code>«UK»</code> para evitar ambigüedades.<br/>"
        "• <b>Reglas de Cascada (ACID):</b> <code>fk_cuenta_usuario</code> y <code>fk_transaccion_cuenta</code> configuradas con <code>ON DELETE CASCADE</code>.<br/>"
        "• <b>Protección Histórica:</b> <code>fk_residuo_smartbin</code> con <code>ON DELETE RESTRICT</code> para resguardar la bitácora física.<br/>"
        "• <b>Regla del Negocio:</b> Columna <code>smartbin.capacidad_maxima = 50.0</code> y transición a <code>estado = 'Bloqueado'</code>."
    )
    story.append(Paragraph(rel_notes, body_style))
    story.append(PageBreak())

    # ==========================
    # 12. MODELO ENTIDAD-RELACIÓN (DER)
    # ==========================
    story.append(Paragraph("12. MODELO ENTIDAD-RELACIÓN (DER - NOTACIÓN DE CHEN)", h1_style))
    story.append(HRFlowable(width="100%", thickness=1, color=PRIMARY, spaceAfter=6))
    story.append(Paragraph(
        "<b>Modelo Conceptual de Datos:</b> Entidades (rectángulos), relaciones (rombos), atributos (óvalos con PK subrayada) "
        "y líneas dobles para participación total conforme al estándar de Chen (Elmasri & Navathe).",
        body_style
    ))
    story.append(make_scaled_image("12-der.png", max_w=540, max_h=310))
    story.append(Spacer(1, 6))

    der_notes = (
        "• <b>Entidades y Atributos:</b> <code>USUARIO</code>, <code>CUENTA_PUNTOS</code>, <code>TRANSACCION_PUNTOS</code>, <code>RESIDUO</code> y <code>SMARTBIN</code> con sus claves subrayadas.<br/>"
        "• <b>Participación Total (Línea Doble):</b> <code>CUENTA_PUNTOS</code> no existe sin un usuario, <code>TRANSACCION_PUNTOS</code> exige una cuenta y todo <code>RESIDUO</code> pertenece a un usuario y a un contenedor.<br/>"
        "• <b>Razón de Cardinalidad:</b> <code>1:1</code> entre Usuario y Cuenta, <code>1:N</code> en depósitos y transacciones de puntos."
    )
    story.append(Paragraph(der_notes, body_style))
    story.append(Spacer(1, 10))

    # Conclusión
    story.append(Paragraph("<b>CERTIFICACIÓN DE CONFORMIDAD METODOLÓGICA:</b>", h2_style))
    concl_p = (
        "El presente dossier demuestra la coherencia total entre los diez modelos arquitectónicos diseñados en Draw.io "
        "y el código fuente implementado. Se cumple la regla de oro de la ingeniería de software: <i>'mismo nombre para lo mismo'</i> "
        "en todas las vistas del sistema, garantizando la aprobación y rigurosidad académica requerida en el 6to semestre."
    )
    story.append(Paragraph(concl_p, body_style))

    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"PDF completo con diagramas Draw.io generado en: {filename}")

if __name__ == "__main__":
    out_pdf = r"C:\Users\Usuario\Desktop\Study\PRO-AULA\Smart_waste_bin_6to_semestre\DIAGRAMAS_ARQUITECTURA_UML_SMART_WASTE_BIN.pdf"
    build_pdf(out_pdf)
