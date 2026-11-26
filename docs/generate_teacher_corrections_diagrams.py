# -*- coding: utf-8 -*-
"""Actualización y adición de diagramas solicitados por el docente:
- 06-componentes: mejora de simbología (interfaces lollipop ○ provistas y socket Ɑ requeridas)
- 11-modelo-relacional: tablas con perfil Ambler («PK», «FK», «UK», tipos, FKs con multiplicidad)
- 12-der: modelo entidad-relación en notación Chen (entidades, rombos, elipses, participación total)
"""
import sys
import os

sys.path.insert(0, r"C:\Users\Usuario\Desktop\Study\Electiva II\boveda\Mi Trabajo\.opencode\skills\diagramas-uml\scripts")
from drawio_lib import *

OUT_DIR = r"C:\Users\Usuario\Desktop\Study\PRO-AULA\Smart_waste_bin_6to_semestre\diagramas"
os.makedirs(OUT_DIR, exist_ok=True)

# ==============================================================================
# 06. COMPONENTES (UML 11.6 - CON SIMBOLOGÍA DE INTERFACES PROVISTAS Y REQUERIDAS)
# ==============================================================================
def gen_06_componentes_mejorado():
    d = Diagrama("Componentes", 1600, 1050)
    d.titulo("Diagrama de componentes — Smart Waste Bin (SWB)",
             "UML 2.5.1 cláusula 11.6 y 10.4 — Interfaz provista (bola ○) y requerida (socket Ɑ), icono UML 2 en esquina")
    
    d.marco_diagrama("cmp Arquitectura de tres capas con ensambles de interfaces", 40, 80, 1520, 930)
    
    # Capa Presentación
    c_mov = d.componente("App Móvil Ciudadana", 80, 180, 260, 90,
                         detalle="Android SDK 34 / Java\nVolley REST Client", color=AZUL)
    
    c_web = d.componente("Dashboard Web Admin", 80, 420, 260, 90,
                         detalle="HTML5 / Glassmorphism CSS\nMapa Interactivo JS", color=AZUL)
    
    c_iot = d.componente("Estación IoT SmartBin", 80, 680, 260, 90,
                         detalle="Firmware ESP32 / Arduino\nSensores HX711 y HC-SR04", color=AMARILLO)
    
    # Ensamble Capa Cliente -> API Gateway
    # API Gateway provee REST API (bola ○ hacia la izquierda)
    # Móvil y Web requieren REST API (socket Ɑ hacia la derecha)
    c_gate = d.componente("API Gateway / Router", 600, 290, 270, 100,
                          detalle="Enrutador REST / CORS\nJSON Serializer", color=VERDE)
    
    # Sockets en clientes (requerida)
    d.interfaz_requerida(340, 225, "", lado="der", largo=60)
    d.interfaz_requerida(340, 465, "", lado="der", largo=60)
    d.interfaz_requerida(340, 725, "", lado="der", largo=60)
    
    # Lollipops en Gateway (provistas)
    d.interfaz_provista(600, 225, "API REST Móvil\n(HTTP/JSON)", lado="izq", largo=120)
    d.interfaz_provista(600, 340, "API Web Admin\n(HTTP/JSON)", lado="izq", largo=120)
    d.interfaz_provista(600, 465, "Ingesta Telemetría\n(HTTP POST)", lado="izq", largo=120)
    
    # Líneas de ensamble entre socket y lollipop
    d.flecha(409, 225, 472, 225, "endArrow=none;html=1;strokeColor=#333333;")
    d.flecha(409, 465, 472, 465, "endArrow=none;html=1;strokeColor=#333333;")
    d.flecha(409, 725, 472, 465, "endArrow=none;html=1;strokeColor=#333333;")
    
    # Submódulos del Backend
    c_auth = d.componente("Módulo Autenticación", 1020, 140, 250, 80,
                          detalle="login.php / registro.php\nGenerador QR Carné", color=VERDE)
    
    c_depo = d.componente("Módulo de Depósitos", 1020, 260, 250, 80,
                          detalle="depositar.php\nControl Tope 50.0 kg", color=VERDE)
    
    c_ptos = d.componente("Motor de Puntos / Canje", 1020, 380, 250, 80,
                          detalle="10 pts = $100 COP\ncanjear_puntos.php", color=VERDE)
    
    c_tele = d.componente("Módulo de Telemetría", 1020, 500, 250, 80,
                          detalle="get_bins.php / vaciar_bin.php\nMonitoreo de Nivel", color=VERDE)
    
    # Conexiones Gateway -> Módulos
    d.arista(c_gate, c_auth, DEPENDENCIA, valor="«delegate»")
    d.arista(c_gate, c_depo, DEPENDENCIA, valor="«delegate»")
    d.arista(c_gate, c_ptos, DEPENDENCIA, valor="«delegate»")
    d.arista(c_gate, c_tele, DEPENDENCIA, valor="«delegate»")
    
    # Capa de Acceso a Datos
    c_pdo = d.componente("Capa Abstracción PDO / MySQL", 1020, 680, 250, 80,
                         detalle="Conexión Transaccional ACID\nPrepared Statements", color=BLANCO)
    
    # Módulos requieren interfaz de persistencia
    d.arista(c_auth, c_pdo, DEPENDENCIA, valor="«use»")
    d.arista(c_depo, c_pdo, DEPENDENCIA, valor="«use»")
    d.arista(c_ptos, c_pdo, DEPENDENCIA, valor="«use»")
    d.arista(c_tele, c_pdo, DEPENDENCIA, valor="«use»")
    
    # Base de Datos MariaDB
    c_db = d.componente("Base de Datos MariaDB", 1020, 860, 250, 90,
                        detalle="bd_smartbin (Motor InnoDB)\nRestricciones e Índices", color=GRIS)
    
    # Ensamble PDO -> Base de datos mediante lollipop / socket
    d.interfaz_requerida(1145, 760, "", lado="der", largo=30)
    d.interfaz_provista(1145, 860, "TCP/IP 3306\nSQL Socket", lado="der", largo=30)
    d.flecha(1183, 760, 1183, 822, "endArrow=none;html=1;strokeColor=#333333;")
    
    # Nota explicativa de simbología UML 2
    d.nota("Simbología OMG UML 2.5.1:\n• Ícono en esquina: Clasificador «component»\n• Bola (○): Interfaz provista (realización)\n• Medio círculo (Ɑ): Interfaz requerida (uso)\n• Ensamble: El socket abraza la bola",
           1310, 150, 230, 95)
    
    drawio = os.path.join(OUT_DIR, "06-componentes.drawio")
    png = os.path.join(OUT_DIR, "06-componentes.png")
    d.guardar(drawio, png)
    print("06 Componentes (simbología mejorada) generado.")

# ==============================================================================
# 11. MODELO RELACIONAL (PERFIL DE DATOS DE AMBLER)
# ==============================================================================
def gen_11_modelo_relacional():
    d = Diagrama("Modelo relacional", 1750, 1000)
    d.titulo("Modelo relacional — Base de Datos bd_smartbin",
             "Tablas normalizadas, claves primarias «PK», claves foráneas «FK» y únicas «UK» (Perfil Ambler)")
    
    d.marco_diagrama("rel Esquema físico relacional en motor MariaDB InnoDB", 40, 80, 1670, 880)
    
    # Tabla: usuario
    t_usr = d.clase("usuario", 80, 160, 340,
                    atributos=[
                        "«PK» id_usuario: INT",
                        "«UK» correo: VARCHAR(100)",
                        "«UK» usuario: VARCHAR(50)",
                        "nombre: VARCHAR(100)",
                        "rol: VARCHAR(20)",
                        "qr_code: VARCHAR(255) NULL",
                        "fecha_registro: DATETIME"
                    ],
                    color=AZUL)
    
    # Tabla: cuenta_puntos
    t_cta = d.clase("cuenta_puntos", 560, 160, 360,
                    atributos=[
                        "«PK» id_cuenta: INT AUTO_INCREMENT",
                        "«FK» id_usuario: INT",
                        "saldo_actual: INT (default: 0)",
                        "fecha_actualizacion: DATETIME"
                    ],
                    color=VERDE)
    
    # Tabla: transaccion_puntos
    t_trx = d.clase("transaccion_puntos", 1080, 160, 380,
                    atributos=[
                        "«PK» id_transaccion: INT AUTO_INCREMENT",
                        "«FK» id_cuenta: INT",
                        "tipo: VARCHAR(50)",
                        "cantidad: INT",
                        "fecha: DATETIME"
                    ],
                    color=VERDE)
    
    # Tabla: smartbin
    t_bin = d.clase("smartbin", 80, 560, 360,
                    atributos=[
                        "«PK» id_bin: INT AUTO_INCREMENT",
                        "ubicacion: VARCHAR(150)",
                        "capacidad_maxima: FLOAT (default: 50.0)",
                        "nivel_actual: FLOAT (default: 0.0)",
                        "estado: VARCHAR(50) (Activo/Bloqueado)"
                    ],
                    color=AMARILLO)
    
    # Tabla: residuo
    t_res = d.clase("residuo", 560, 560, 360,
                    atributos=[
                        "«PK» id_residuo: INT AUTO_INCREMENT",
                        "«FK» id_usuario: INT",
                        "«FK» id_bin: INT",
                        "tipo: VARCHAR(50)",
                        "peso: FLOAT",
                        "fecha: DATETIME"
                    ],
                    color=AZUL)
    
    # Relaciones foráneas con multiplicidades
    # usuario 1 -> cuenta_puntos 1 (ON DELETE CASCADE)
    d.arista(t_usr, t_cta, ASOCIACION, valor="fk_cuenta_usuario",
             etiquetas=[(-0.85, "1", "left", "bottom"), (0.85, "1", "right", "bottom")])
    
    # cuenta_puntos 1 -> transaccion_puntos 0..* (ON DELETE CASCADE)
    d.arista(t_cta, t_trx, ASOCIACION, valor="fk_transaccion_cuenta",
             etiquetas=[(-0.85, "1", "left", "bottom"), (0.85, "0..*", "right", "bottom")])
    
    # usuario 1 -> residuo 0..* (ON DELETE CASCADE)
    d.arista(t_usr, t_res, ASOCIACION, valor="fk_residuo_usuario",
             etiquetas=[(-0.85, "1", "left", "bottom"), (0.85, "0..*", "right", "bottom")])
    
    # smartbin 1 -> residuo 0..* (ON DELETE RESTRICT)
    d.arista(t_bin, t_res, ASOCIACION, valor="fk_residuo_smartbin",
             etiquetas=[(-0.85, "1", "left", "bottom"), (0.85, "0..*", "right", "bottom")])
    
    # Tabla resumen de integridad y constraints
    d.nota("Reglas de Integridad Referencial (bd_smartbin):\n"
           "• fk_cuenta_usuario: ON DELETE CASCADE (un usuario posee 1 monedero)\n"
           "• fk_transaccion_cuenta: ON DELETE CASCADE (auditoría en cascada)\n"
           "• fk_residuo_usuario: ON DELETE CASCADE\n"
           "• fk_residuo_smartbin: ON DELETE RESTRICT (historial de pesaje protegido)\n"
           "• Regla de Negocio: capacidad_maxima = 50.0 kg; si nivel_actual >= 50.0 -> estado = 'Bloqueado'",
           1080, 560, 480, 110)
    
    drawio = os.path.join(OUT_DIR, "11-modelo-relacional.drawio")
    png = os.path.join(OUT_DIR, "11-modelo-relacional.png")
    d.guardar(drawio, png)
    print("11 Modelo Relacional generado.")

# ==============================================================================
# 12. MODELO ENTIDAD-RELACIÓN (DER) EN NOTACIÓN DE CHEN
# ==============================================================================
def gen_12_der():
    d = Diagrama("DER", 1700, 1100)
    d.titulo("Modelo entidad-relación (DER) — Smart Waste Bin (SWB)",
             "Notación de Chen (Elmasri & Navathe) · Entidades (rectángulos), relaciones (rombos), atributos (óvalos) y cardinalidades")
    
    d.marco_diagrama("der Modelo conceptual de datos en notación de Chen", 40, 80, 1620, 980)
    
    # Entidades (Rectángulos)
    e_usr = d.entidad("USUARIO", 160, 320, 180, 60, color=AZUL)
    e_cta = d.entidad("CUENTA_PUNTOS", 680, 320, 200, 60, color=VERDE)
    e_trx = d.entidad("TRANSACCION_PUNTOS", 1240, 320, 220, 60, color=VERDE)
    e_res = d.entidad("RESIDUO", 690, 720, 180, 60, color=AZUL)
    e_bin = d.entidad("SMARTBIN", 160, 720, 180, 60, color=AMARILLO)
    
    # Relaciones (Rombos)
    r_pos = d.relacion("posee", 440, 313, 130, 74)
    r_aud = d.relacion("registra", 990, 313, 130, 74)
    r_dep = d.relacion("deposita", 440, 520, 130, 74)
    r_rec = d.relacion("recepciona", 440, 713, 130, 74)
    
    # Conexiones Entidad - Relación con cardinalidades y participación total (doble línea)
    # USUARIO (1, parcial) --- posee --- (1, total) CUENTA_PUNTOS
    d.linea_er(e_usr, r_pos, cardinalidad="1", total=False)
    d.linea_er(e_cta, r_pos, cardinalidad="1", total=True)
    
    # CUENTA_PUNTOS (1, parcial) --- registra --- (N, total) TRANSACCION_PUNTOS
    d.linea_er(e_cta, r_aud, cardinalidad="1", total=False)
    d.linea_er(e_trx, r_aud, cardinalidad="N", total=True)
    
    # USUARIO (1, parcial) --- deposita --- (N, total) RESIDUO
    d.linea_er(e_usr, r_dep, cardinalidad="1", total=False)
    d.linea_er(e_res, r_dep, cardinalidad="N", total=True)
    
    # SMARTBIN (1, parcial) --- recepciona --- (N, total) RESIDUO
    d.linea_er(e_bin, r_rec, cardinalidad="1", total=False)
    d.linea_er(e_res, r_rec, cardinalidad="N", total=True)
    
    # Atributos de USUARIO (Óvalos)
    a_u1 = d.atributo("id_usuario", 40, 200, 110, 40, clave=True)
    a_u2 = d.atributo("nombre", 170, 180, 110, 40)
    a_u3 = d.atributo("correo", 300, 200, 110, 40)
    a_u4 = d.atributo("rol", 40, 430, 110, 40)
    a_u5 = d.atributo("qr_code", 170, 450, 110, 40)
    
    d.linea_er(e_usr, a_u1)
    d.linea_er(e_usr, a_u2)
    d.linea_er(e_usr, a_u3)
    d.linea_er(e_usr, a_u4)
    d.linea_er(e_usr, a_u5)
    
    # Atributos de CUENTA_PUNTOS
    a_c1 = d.atributo("id_cuenta", 640, 190, 110, 40, clave=True)
    a_c2 = d.atributo("saldo_actual", 780, 190, 110, 40)
    d.linea_er(e_cta, a_c1)
    d.linea_er(e_cta, a_c2)
    
    # Atributos de TRANSACCION_PUNTOS
    a_t1 = d.atributo("id_transaccion", 1200, 190, 120, 40, clave=True)
    a_t2 = d.atributo("tipo", 1340, 190, 110, 40)
    a_t3 = d.atributo("cantidad", 1340, 440, 110, 40)
    a_t4 = d.atributo("fecha", 1200, 440, 110, 40)
    d.linea_er(e_trx, a_t1)
    d.linea_er(e_trx, a_t2)
    d.linea_er(e_trx, a_t3)
    d.linea_er(e_trx, a_t4)
    
    # Atributos de RESIDUO
    a_r1 = d.atributo("id_residuo", 620, 850, 110, 40, clave=True)
    a_r2 = d.atributo("tipo", 740, 860, 110, 40)
    a_r3 = d.atributo("peso", 860, 850, 110, 40)
    a_r4 = d.atributo("fecha", 910, 730, 110, 40)
    d.linea_er(e_res, a_r1)
    d.linea_er(e_res, a_r2)
    d.linea_er(e_res, a_r3)
    d.linea_er(e_res, a_r4)
    
    # Atributos de SMARTBIN
    a_b1 = d.atributo("id_bin", 40, 620, 110, 40, clave=True)
    a_b2 = d.atributo("ubicacion", 40, 840, 110, 40)
    a_b3 = d.atributo("capacidad_max", 160, 860, 120, 40)
    a_b4 = d.atributo("nivel_actual", 290, 840, 110, 40)
    a_b5 = d.atributo("estado", 290, 620, 110, 40)
    d.linea_er(e_bin, a_b1)
    d.linea_er(e_bin, a_b2)
    d.linea_er(e_bin, a_b3)
    d.linea_er(e_bin, a_b4)
    d.linea_er(e_bin, a_b5)
    
    # Nota de convenciones Chen
    d.nota("Convenciones Notación de Chen:\n"
           "• Rectángulo: Entidad física o conceptual\n"
           "• Rombo: Relación entre entidades\n"
           "• Óvalo subrayado: Atributo Clave Primaria (PK)\n"
           "• Línea doble: Participación Total (existencia obligatoria)\n"
           "• 1:1, 1:N: Razón de cardinalidad",
           1080, 560, 360, 110)
    
    drawio = os.path.join(OUT_DIR, "12-der.drawio")
    png = os.path.join(OUT_DIR, "12-der.png")
    d.guardar(drawio, png)
    print("12 DER (Notación de Chen) generado.")

if __name__ == "__main__":
    print("Generando mejoras de diagramas...")
    gen_06_componentes_mejorado()
    gen_11_modelo_relacional()
    gen_12_der()
    print("¡Diagramas generados y exportados exitosamente!")
