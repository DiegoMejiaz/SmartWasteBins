# -*- coding: utf-8 -*-
"""Generador de los 10 Diagramas de Arquitectura UML para Smart Waste Bin (SWB)
Cumple con la norma OMG UML 2.5.1 y las convenciones de la bóveda de Obsidian.
Utiliza drawio_lib.py para generar los archivos .drawio y exportar los .png oficiales.
"""
import sys
import os
import pathlib

# Importar drawio_lib de la bóveda
sys.path.insert(0, r"C:\Users\Usuario\Desktop\Study\Electiva II\boveda\Mi Trabajo\.opencode\skills\diagramas-uml\scripts")
from drawio_lib import *

OUT_DIR = r"C:\Users\Usuario\Desktop\Study\PRO-AULA\Smart_waste_bin_6to_semestre\diagramas"
os.makedirs(OUT_DIR, exist_ok=True)

# ==============================================================================
# 01. CASOS DE USO (UML 18)
# ==============================================================================
def gen_01_casos_de_uso():
    d = Diagrama("Casos de uso", 1600, 1100)
    d.titulo("Diagrama de casos de uso — Smart Waste Bin (SWB)",
             "UML 2.5.1 cláusula 18 — Actores principales a la izquierda, sistema IoT a la derecha")
    
    # Sujeto
    s = d.sujeto("Smart Waste Bin (SWB)", 360, 90, 880, 960, palabra_clave="subsystem")
    
    # Actores
    ciu = d.actor("Ciudadano\n(App Móvil)", 100, 360, tipo="humano")
    adm = d.actor("Administrador\n(Web Admin)", 100, 780, tipo="humano")
    iot = d.actor("Estación IoT\n(Sensores / Bin)", 1360, 420, tipo="hardware")
    
    # Casos de uso
    cu_log = d.caso_uso("UC-01: Iniciar sesión /\nAutenticar usuario", 420, 150)
    cu_reg = d.caso_uso("UC-02: Registrar nueva\ncuenta ciudadana", 720, 150)
    cu_qr  = d.caso_uso("UC-03: Escanear / Identificar\nSmartBin (QR)", 420, 290)
    cu_dep = d.caso_uso("UC-04: Depositar material\nreciclable", 420, 430)
    cu_pes = d.caso_uso("UC-05: Pesar y validar\nresiduo (HX711)", 760, 430)
    cu_pto = d.caso_uso("UC-06: Liquidar y acreditar\npuntos ecológicos", 760, 560)
    cu_blo = d.caso_uso("UC-10: Bloquear estación\npor saturación (>=50kg)", 760, 310)
    cu_bil = d.caso_uso("UC-07: Consultar billetera,\nhistorial y leaderboard", 420, 580)
    cu_can = d.caso_uso("UC-08: Canjear puntos por\ndinero (10 pts = $100 COP)", 420, 720)
    cu_map = d.caso_uso("UC-09: Monitorear telemetría\ny estado de bins en mapa", 420, 860)
    cu_vac = d.caso_uso("UC-11: Registrar vaciado\ny calibración de tara", 760, 860)
    cu_rep = d.caso_uso("UC-12: Gestionar usuarios\ny reportes operativos", 760, 720)
    
    # Nota de condición para «extend»
    n_blo = d.nota("condition: {nivel >= 50.0 kg}\nextension point: saturación", 1080, 270, 180, 50)
    
    # Asociaciones Ciudadano
    d.arista(ciu, cu_log, ASOCIACION)
    d.arista(ciu, cu_reg, ASOCIACION)
    d.arista(ciu, cu_qr, ASOCIACION)
    d.arista(ciu, cu_dep, ASOCIACION)
    d.arista(ciu, cu_bil, ASOCIACION)
    d.arista(ciu, cu_can, ASOCIACION)
    
    # Asociaciones Admin
    d.arista(adm, cu_log, ASOCIACION)
    d.arista(adm, cu_map, ASOCIACION)
    d.arista(adm, cu_vac, ASOCIACION)
    d.arista(adm, cu_rep, ASOCIACION)
    
    # Asociaciones IoT
    d.arista(iot, cu_pes, ASOCIACION)
    d.arista(iot, cu_blo, ASOCIACION)
    
    # «include» y «extend»
    d.arista(cu_dep, cu_pes, INCLUDE)
    d.arista(cu_pes, cu_pto, INCLUDE)
    
    # «extend» apunta del caso que extiende (cu_blo) al base (cu_pes)
    ar_ext = d.arista(cu_blo, cu_pes, "endArrow=open;dashed=1;html=1;strokeColor=#333333;",
                      etiquetas=[(-0.1, "«extend»", "center")])
    d.ancla(n_blo, cu_blo)
    
    drawio = os.path.join(OUT_DIR, "01-casos-de-uso.drawio")
    png = os.path.join(OUT_DIR, "01-casos-de-uso.png")
    d.guardar(drawio, png)
    print("01 Casos de uso generado.")

# ==============================================================================
# 02. CLASES (UML 9, 11)
# ==============================================================================
def gen_02_clases():
    d = Diagrama("Clases", 1700, 1100)
    d.titulo("Diagrama de clases — Smart Waste Bin (SWB)",
             "UML 2.5.1 cláusulas 9 y 11 — Dominio, tipos de datos, visibilidad y multiplicidades completas")
    
    # Clases
    c_usr = d.clase("Usuario", 80, 160, 270,
                    atributos=[
                        "- id_usuario: int",
                        "- nombre: String",
                        "- correo: String",
                        "- usuario: String",
                        "- contrasena_hash: String",
                        "- rol: String",
                        "- qr_code: String [0..1]",
                        "- fecha_registro: DateTime"
                    ],
                    operaciones=[
                        "+ registrar(): bool",
                        "+ autenticar(): bool",
                        "+ actualizarPerfil(): bool",
                        "+ generarCarnetQR(): String"
                    ], color=AZUL)
    
    c_cta = d.clase("CuentaPuntos", 480, 160, 260,
                    atributos=[
                        "- id_cuenta: int",
                        "- saldo_actual: int",
                        "- fecha_actualizacion: DateTime"
                    ],
                    operaciones=[
                        "+ acreditarPuntos(cantidad: int): bool",
                        "+ debitarPuntos(cantidad: int): bool",
                        "+ consultarSaldo(): int",
                        "+ obtenerRango(): String"
                    ], color=VERDE)
    
    c_trx = d.clase("TransaccionPuntos", 880, 160, 280,
                    atributos=[
                        "- id_transaccion: int",
                        "- tipo: String",
                        "- cantidad: int",
                        "- fecha: DateTime"
                    ],
                    operaciones=[
                        "+ auditarMovimiento(): void",
                        "+ exportarComprobante(): String"
                    ], color=VERDE)
    
    c_bin = d.clase("SmartBin", 80, 620, 290,
                    atributos=[
                        "- id_bin: int",
                        "- ubicacion: String",
                        "- capacidad_maxima: float = 50.0",
                        "- nivel_actual: float",
                        "- estado: EstadoBin",
                        "- latitud: float",
                        "- longitud: float"
                    ],
                    operaciones=[
                        "+ verificarCapacidad(peso: float): bool",
                        "+ registrarCarga(peso: float): void",
                        "+ bloquearPorSaturacion(): void",
                        "+ registrarVaciado(): void",
                        "+ obtenerPorcentaje(): float"
                    ], color=AMARILLO)
    
    c_res = d.clase("Residuo", 480, 620, 270,
                    atributos=[
                        "- id_residuo: int",
                        "- tipo: TipoResiduo",
                        "- peso: float",
                        "- fecha: DateTime"
                    ],
                    operaciones=[
                        "+ calcularPuntosEquivalentes(): int",
                        "+ guardarRegistro(): bool"
                    ], color=AZUL)
    
    c_cnj = d.clase("CanjeRecompensa", 480, 420, 270,
                    atributos=[
                        "- id_canje: int",
                        "- puntos_canjeados: int",
                        "- valor_cop: float",
                        "- numero_telefono: String",
                        "- plataforma: String",
                        "- fecha: DateTime"
                    ],
                    operaciones=[
                        "+ validarNumero(tel: String): bool",
                        "+ procesarCanje(): bool"
                    ], color=VIOLETA)
    
    # Enumeraciones
    e_tip = d.clase("TipoResiduo", 880, 620, 240,
                    atributos=[
                        "PLASTICO (10 pts/kg)",
                        "VIDRIO (15 pts/kg)",
                        "ELECTRONICO (50 pts/kg)",
                        "PAPEL_CARTON (5 pts/kg)"
                    ], estereotipo="enumeration", color=GRIS)
    
    e_est = d.clase("EstadoBin", 80, 940, 200,
                    atributos=[
                        "Activo",
                        "Bloqueado"
                    ], estereotipo="enumeration", color=GRIS)
    
    # Servicio API Cliente Móvil
    c_api = d.clase("ApiClientService", 880, 420, 280,
                    atributos=[
                        "- baseUrl: String"
                    ],
                    operaciones=[
                        "+ postLogin(user: String, pass: String)",
                        "+ postDepositar(idU: int, idB: int, ...)",
                        "+ getSaldoPuntos(idU: int)",
                        "+ postCanje(idU: int, pts: int, tel: String)"
                    ], color=BLANCO)
    
    # Relaciones con rombos y multiplicidades exactas
    # Usuario 1 *-- 1 CuentaPuntos (Composición: rombo en Usuario)
    d.arista(c_usr, c_cta, COMPOSICION,
             etiquetas=[(-0.85, "1", "left", "bottom"), (0.85, "1", "right", "bottom")])
    
    # CuentaPuntos 1 o-- 0..* TransaccionPuntos (Agregación: rombo en Cuenta)
    d.arista(c_cta, c_trx, AGREGACION,
             etiquetas=[(-0.85, "1", "left", "bottom"), (0.85, "0..*", "right", "bottom")])
    
    # Usuario 1 -- 0..* Residuo
    d.arista(c_usr, c_res, ASOCIACION,
             etiquetas=[(-0.85, "1", "left", "bottom"), (0.85, "0..*", "right", "bottom")])
    
    # SmartBin 1 -- 0..* Residuo
    d.arista(c_bin, c_res, ASOCIACION,
             etiquetas=[(-0.85, "1", "left", "bottom"), (0.85, "0..*", "right", "bottom")])
    
    # Usuario 1 -- 0..* Canje
    d.arista(c_usr, c_cnj, ASOCIACION,
             etiquetas=[(-0.85, "1", "left", "bottom"), (0.85, "0..*", "right", "bottom")])
    
    # Dependencia Residuo ..> TipoResiduo
    d.arista(c_res, e_tip, DEPENDENCIA)
    
    # Dependencia SmartBin ..> EstadoBin
    d.arista(c_bin, e_est, DEPENDENCIA)
    
    # Dependencia ApiClientService ..> CanjeRecompensa
    d.arista(c_api, c_cnj, DEPENDENCIA)
    
    drawio = os.path.join(OUT_DIR, "02-clases.drawio")
    png = os.path.join(OUT_DIR, "02-clases.png")
    d.guardar(drawio, png)
    print("02 Clases generado.")

# ==============================================================================
# 03. SECUENCIA (UML 17)
# ==============================================================================
def gen_03_secuencia():
    d = Diagrama("Secuencia", 1600, 1100)
    d.titulo("Diagrama de secuencia — Depósito de Residuo y Liquidación",
             "UML 2.5.1 cláusula 17 — Validación de capacidad (50kg), atomismo transaccional y bloqueo")
    
    # Marco sd
    d.marco_diagrama("sd Depósito de residuo y acreditación de puntos", 40, 80, 1520, 980)
    
    # Participantes y líneas de vida
    # Actor ciudadano
    d.actor_de_secuencia("Ciudadano", 120, 120, 1020)
    
    l_ui  = d.linea_de_vida(":DepositarActivity", 260, 120, 140, 900, color=AZUL)
    l_cli = d.linea_de_vida(":ApiClient", 520, 120, 130, 900, color=AZUL)
    l_api = d.linea_de_vida(":ApiRouter\n(depositar.php)", 770, 120, 140, 900, color=VERDE)
    l_bin = d.linea_de_vida(":SmartBin", 1040, 120, 120, 900, color=AMARILLO)
    l_db  = d.linea_de_vida(":MariaDB\n(bd_smartbin)", 1290, 120, 130, 900, color=GRIS)
    
    # Activaciones
    act_ui  = d.activacion(330, 200, 960)
    act_cli = d.activacion(585, 230, 930)
    act_api = d.activacion(840, 260, 900)
    act_bin = d.activacion(1100, 300, 380)
    act_db1 = d.activacion(1355, 330, 370)
    
    # 1. Ciudadano -> UI
    d.flecha(120, 200, 324, 200, MENSAJE, "1: seleccionarBinYMaterial(idBin, tipo, peso)")
    
    # 2. UI -> ApiClient
    d.flecha(336, 230, 579, 230, MENSAJE, "2: postDepositar(payloadJSON)")
    
    # 3. ApiClient -> ApiRouter
    d.flecha(591, 260, 834, 260, MENSAJE, "3: POST /api/depositar.php")
    
    # 4. ApiRouter -> SmartBin
    d.flecha(846, 300, 1094, 300, MENSAJE, "4: verificarCapacidad(peso)")
    
    # 5. SmartBin -> MariaDB
    d.flecha(1106, 330, 1349, 330, MENSAJE, "5: SELECT capacidad_maxima, nivel_actual")
    d.flecha(1349, 360, 1106, 360, RESPUESTA, "cap_max=50.0, nivel_actual=48.5")
    
    # 6. SmartBin -> ApiRouter
    d.flecha(1094, 380, 846, 380, RESPUESTA, "capacidad_disponible: bool")
    
    # Fragmento combinado ALT
    f_alt = d.fragmento("alt", 220, 420, 1280, 480)
    d.guarda("[nivel_actual + peso > 50.0 kg]", 290, 430)
    
    # Rama fallo
    d.flecha(834, 460, 591, 460, RESPUESTA, "HTTP 400 Bad Request {error: 'Capacidad excedida'}")
    d.flecha(579, 490, 336, 490, RESPUESTA, "callbackError(mensaje)")
    d.flecha(324, 520, 120, 520, RESPUESTA, "mostrarAlerta('Contenedor saturado')")
    
    # Separador else
    d.separador(220, 1500, 560, "[else: depósito admitido]")
    
    # Rama éxito
    act_db2 = d.activacion(1355, 600, 830)
    d.flecha(846, 600, 1349, 600, MENSAJE, "6: BEGIN TRANSACTION")
    d.flecha(846, 640, 1349, 640, MENSAJE, "7: INSERT INTO residuo (...)")
    d.flecha(846, 680, 1349, 680, MENSAJE, "8: UPDATE smartbin SET nivel_actual = ...")
    
    # Fragmento OPT (si llega a 50kg, bloquear)
    f_opt = d.fragmento("opt", 800, 710, 680, 60)
    d.guarda("[nuevo_nivel >= 50.0 kg]", 870, 715)
    d.flecha(846, 740, 1349, 740, MENSAJE, "9: UPDATE smartbin SET estado = 'Bloqueado'")
    
    d.flecha(846, 785, 1349, 785, MENSAJE, "10: UPDATE cuenta_puntos & INSERT transaccion")
    d.flecha(846, 820, 1349, 820, MENSAJE, "11: COMMIT TRANSACTION")
    
    d.flecha(834, 850, 591, 850, RESPUESTA, "HTTP 200 OK {puntos: 25, saldo: 135}")
    d.flecha(579, 880, 336, 880, RESPUESTA, "callbackExito(puntos, saldo)")
    d.flecha(324, 910, 120, 910, RESPUESTA, "mostrarComprobante('+25 puntos acreditados')")
    
    drawio = os.path.join(OUT_DIR, "03-secuencia.drawio")
    png = os.path.join(OUT_DIR, "03-secuencia.png")
    d.guardar(drawio, png)
    print("03 Secuencia generado.")

# ==============================================================================
# 04. ACTIVIDADES (UML 15)
# ==============================================================================
def gen_04_actividades():
    d = Diagrama("Actividades", 1200, 1200)
    d.titulo("Diagrama de actividades — Ciclo Operativo de Depósito y Reciclaje",
             "UML 2.5.1 cláusula 15 — Nodos de acción, decisiones, ramas y guardas operativas")
    
    d.marco_diagrama("act Ciclo de depósito en estación SmartBin", 40, 80, 1120, 1080)
    
    ini = d.inicio(580, 120)
    a1  = d.accion("Ciudadano abre app y escanea\ncódigo QR de la estación", 480, 180, 240, 50, color=AZUL)
    a2  = d.accion("Seleccionar tipo de residuo\n(Plástico, Vidrio, Electrónico, Papel)", 470, 270, 260, 50, color=AZUL)
    a3  = d.accion("Colocar residuo en la compuerta;\nSensor HX711 mide el peso", 470, 360, 260, 50, color=AMARILLO)
    
    dec1 = d.decision(585, 450)
    err1 = d.accion("Notificar error:\n'Objeto no detectado o peso cero'", 180, 445, 220, 50, color=ROJO)
    fin1 = d.fin_de_flujo(80, 455)
    
    a4   = d.accion("Consultar nivel actual y capacidad\nmáxima del SmartBin (50.0 kg)", 470, 530, 260, 50, color=VERDE)
    dec2 = d.decision(585, 620)
    err2 = d.accion("Bloqueo preventivo:\n'El residuo superaría la capacidad máxima'", 180, 615, 240, 50, color=ROJO)
    fin2 = d.fin_de_flujo(80, 625)
    
    a5   = d.accion("Abrir compuerta e ingresar material;\nCalcular puntos = round(peso * factor)", 470, 700, 260, 50, color=VERDE)
    a6   = d.accion("Registrar residuo, sumar nivel en bin\ny acreditar saldo en cuenta_puntos", 470, 790, 260, 50, color=VERDE)
    
    dec3 = d.decision(585, 880)
    a_blo = d.accion("Actualizar estado = 'Bloqueado'\ny emitir alerta roja en panel web", 180, 875, 240, 50, color=AMARILLO)
    
    a7   = d.accion("Emitir comprobante y actualizar\nsaldo en smartphone", 470, 970, 260, 50, color=AZUL)
    fin_ok = d.fin(585, 1060)
    
    # Flujos
    d.arista(ini, a1, FLUJO)
    d.arista(a1, a2, FLUJO)
    d.arista(a2, a3, FLUJO)
    d.arista(a3, dec1, FLUJO)
    
    d.arista(dec1, err1, FLUJO, etiquetas=[(-0.5, "[peso <= 0.05 kg]", "bottom")])
    d.arista(err1, fin1, FLUJO)
    
    d.arista(dec1, a4, FLUJO, etiquetas=[(0.5, "[peso > 0.05 kg]", "left")])
    d.arista(a4, dec2, FLUJO)
    
    d.arista(dec2, err2, FLUJO, etiquetas=[(-0.5, "[nivel + peso > 50kg]", "bottom")])
    d.arista(err2, fin2, FLUJO)
    
    d.arista(dec2, a5, FLUJO, etiquetas=[(0.5, "[nivel + peso <= 50kg]", "left")])
    d.arista(a5, a6, FLUJO)
    d.arista(a6, dec3, FLUJO)
    
    d.arista(dec3, a_blo, FLUJO, etiquetas=[(-0.5, "[nuevo nivel >= 50kg]", "bottom")])
    d.arista(a_blo, a7, FLUJO)
    d.arista(dec3, a7, FLUJO, etiquetas=[(0.5, "[nuevo nivel < 50kg]", "left")])
    d.arista(a7, fin_ok, FLUJO)
    
    drawio = os.path.join(OUT_DIR, "04-actividades.drawio")
    png = os.path.join(OUT_DIR, "04-actividades.png")
    d.guardar(drawio, png)
    print("04 Actividades generado.")

# ==============================================================================
# 05. ESTADOS (UML 14)
# ==============================================================================
def gen_05_estados():
    d = Diagrama("Estados", 1500, 1000)
    d.titulo("Diagrama de máquina de estados — Ciclo de Vida de SmartBin",
             "UML 2.5.1 cláusula 14 — Transiciones de estado físico con eventos, guardas y acciones")
    
    d.marco_diagrama("stm Ciclo de vida de la estación SmartBin", 40, 80, 1420, 880)
    
    ini = d.inicio(80, 240)
    
    e_vac = d.estado("Disponible (Vacío)", 180, 210, 220, 80,
                     internas=["entry / encenderLEDVerde()", "entry / nivel_actual = 0.0 kg"], color=VERDE)
    
    e_op  = d.estado("Operativo (Carga Parcial)", 540, 210, 240, 80,
                     internas=["do / recibirDepositos()", "do / 0.0 kg < nivel < 40.0 kg"], color=AZUL)
    
    e_alt = d.estado("Alerta (Casi Lleno)", 920, 210, 240, 80,
                     internas=["entry / notificarServidorAseo()", "do / 40.0 kg <= nivel < 50.0 kg"], color=AMARILLO)
    
    e_blo = d.estado("Bloqueado (Saturado)", 920, 480, 240, 80,
                     internas=["entry / enclavarTolvaElectromecanica()", "entry / encenderLEDRojo()"], color=ROJO)
    
    # Estado compuesto
    e_man = d.estado_compuesto("En Mantenimiento (Cuadrilla de Aseo)", 360, 460, 440, 260, color=GRIS)
    
    e_sub1 = d.estado("AperturaTolva", 390, 520, 160, 60, internas=["entry / leerLlaveOperador()"], color=BLANCO)
    e_sub2 = d.estado("ExtraccionResiduos", 600, 520, 170, 60, internas=["do / retirarBolsas()"], color=BLANCO)
    e_sub3 = d.estado("CalibracionTara", 490, 630, 170, 60, internas=["do / taraCeldasHX711()"], color=BLANCO)
    
    d.arista(e_sub1, e_sub2, TRANSICION)
    d.arista(e_sub2, e_sub3, TRANSICION)
    
    e_fuer = d.estado("Fuera de Servicio", 180, 600, 160, 70, internas=["entry / reportarFalla()"], color=ROJO)
    
    # Transiciones principales
    d.arista(ini, e_vac, TRANSICION)
    d.arista(e_vac, e_op, TRANSICION, valor="depositarResiduo(peso) [peso > 0]")
    d.arista(e_op, e_op, TRANSICION, valor="depositarResiduo(peso) [nivel < 40kg]")
    d.arista(e_op, e_alt, TRANSICION, valor="depositarResiduo(peso) [nivel >= 40kg]")
    d.arista(e_alt, e_blo, TRANSICION, valor="depositarResiduo(peso) [nivel >= 50.0kg]")
    
    d.arista(e_blo, e_man, TRANSICION, valor="llegadaOperadorAseo / autenticar()")
    d.arista(e_alt, e_man, TRANSICION, valor="mantenimientoPreventivo()")
    d.arista(e_man, e_vac, TRANSICION, valor="confirmarVaciado() [nivel == 0.0kg]")
    
    d.arista(e_op, e_fuer, TRANSICION, valor="fallaSensor() / timeout")
    d.arista(e_fuer, e_man, TRANSICION, valor="ordenReparacionTecnica()")
    
    drawio = os.path.join(OUT_DIR, "05-estados.drawio")
    png = os.path.join(OUT_DIR, "05-estados.png")
    d.guardar(drawio, png)
    print("05 Estados generado.")

# ==============================================================================
# 06. COMPONENTES (UML 11.6)
# ==============================================================================
def gen_06_componentes():
    d = Diagrama("Componentes", 1600, 1100)
    d.titulo("Diagrama de componentes — Arquitectura de Tres Capas",
             "UML 2.5.1 cláusula 11.6 — Módulos cliente, servicios API REST backend y persistencia MariaDB")
    
    d.marco_diagrama("cmp Arquitectura del software Smart Waste Bin", 40, 80, 1520, 980)
    
    # Capa Cliente
    c_mov = d.componente("SmartWasteBin Mobile App", 100, 180, 280, 110,
                         detalle="Vistas Android Java, Volley HTTP,\nSessionManager (SharedPreferences)", color=AZUL)
    
    c_web = d.componente("Dashboard Web Admin", 100, 420, 280, 110,
                         detalle="Glassmorphism UI, Mapa Interactivo,\nChart.js telemetría", color=AZUL)
    
    c_iot = d.componente("Firmware Estación IoT", 100, 680, 280, 110,
                         detalle="Controlador ESP32, Lecturas HX711\ny Sensor Ultrasonido HC-SR04", color=AMARILLO)
    
    # Capa Backend
    c_gate = d.componente("API Gateway / Router", 560, 320, 280, 110,
                          detalle="Enrutador de peticiones HTTP/REST,\nControl CORS y Headers JSON", color=VERDE)
    
    c_auth = d.componente("Módulo Autenticación", 960, 160, 260, 90,
                          detalle="login.php, registro.php,\ngeneración de QR", color=VERDE)
    
    c_depo = d.componente("Módulo de Depósitos", 960, 290, 260, 90,
                          detalle="depositar.php, validación de\ncapacidad máxima 50.0 kg", color=VERDE)
    
    c_puntos = d.componente("Motor de Gamificación", 960, 420, 260, 90,
                            detalle="Reglas de conversión: 10 pts = $100 COP,\ncanjear_puntos.php", color=VERDE)
    
    c_tele = d.componente("Módulo de Telemetría", 960, 550, 260, 90,
                          detalle="get_bins.php, monitoreo de niveles\ny bloqueo preventivo", color=VERDE)
    
    # Capa Persistencia
    c_pdo = d.componente("Capa Abstracción PDO", 960, 740, 260, 80,
                         detalle="PDO MySQL Wrapper / Transacciones ACID", color=BLANCO)
    
    c_db  = d.componente("Base de Datos MariaDB", 1320, 730, 200, 100,
                         detalle="bd_smartbin (Motor InnoDB)\nTablas normalizadas", color=GRIS)
    
    # Interfaces y conexiones
    # Móvil -> Gateway
    d.arista(c_mov, c_gate, DEPENDENCIA, valor="«use» HTTP/JSON")
    d.arista(c_web, c_gate, DEPENDENCIA, valor="«use» HTTP/JSON")
    d.arista(c_iot, c_gate, DEPENDENCIA, valor="«use» HTTP POST")
    
    # Gateway -> Submódulos
    d.arista(c_gate, c_auth, DEPENDENCIA)
    d.arista(c_gate, c_depo, DEPENDENCIA)
    d.arista(c_gate, c_puntos, DEPENDENCIA)
    d.arista(c_gate, c_tele, DEPENDENCIA)
    
    # Módulos -> PDO
    d.arista(c_auth, c_pdo, DEPENDENCIA)
    d.arista(c_depo, c_pdo, DEPENDENCIA)
    d.arista(c_puntos, c_pdo, DEPENDENCIA)
    d.arista(c_tele, c_pdo, DEPENDENCIA)
    
    # PDO -> BD
    d.arista(c_pdo, c_db, LINK, valor="TCP 3306")
    
    drawio = os.path.join(OUT_DIR, "06-componentes.drawio")
    png = os.path.join(OUT_DIR, "06-componentes.png")
    d.guardar(drawio, png)
    print("06 Componentes generado.")

# ==============================================================================
# 07. DESPLIEGUE (UML 19)
# ==============================================================================
def gen_07_despliegue():
    d = Diagrama("Despliegue", 1600, 1100)
    d.titulo("Diagrama de despliegue — Topología de Nodos e Infraestructura",
             "UML 2.5.1 cláusula 19 — Dispositivos físicos, entornos de ejecución y artefactos desplegados")
    
    d.marco_diagrama("dep Despliegue físico de la plataforma Smart Waste Bin", 40, 80, 1520, 980)
    
    # Nodo Móvil
    n_mov = d.nodo("Dispositivo Móvil Ciudadano", 80, 180, 360, 240,
                   palabra_clave="device", detalle="Smartphone Android 8.0+", color=AZUL)
    d.artefacto("SmartWasteBin.apk", 120, 260, 280, 80, palabra_clave="executable",
                detalle="Bytecode JVM / Dalvik compilado")
    
    # Nodo IoT
    n_iot = d.nodo("Estación Física SmartBin", 80, 580, 360, 320,
                   palabra_clave="device", detalle="Microcontrolador ESP32 / Arduino", color=AMARILLO)
    d.artefacto("firmware_swb.bin", 120, 660, 280, 70, palabra_clave="executable")
    d.artefacto("Sensores: HX711 & HC-SR04", 120, 760, 280, 70, palabra_clave="device")
    
    # Nodo Servidor Web
    n_srv = d.nodo("Servidor Web y Aplicaciones", 640, 280, 420, 480,
                   palabra_clave="device", detalle="Servidor On-Premise / XAMPP", color=VERDE)
    d.artefacto("Apache 2.4 HTTP Server", 680, 370, 340, 70, palabra_clave="executionEnvironment")
    d.artefacto("Intérprete PHP 8.2+", 680, 460, 340, 70, palabra_clave="executionEnvironment")
    d.artefacto("API REST Endpoints (/api/*.php)", 680, 550, 340, 70, palabra_clave="artifact")
    d.artefacto("Portal Glassmorphism (/views/*.php)", 680, 640, 340, 70, palabra_clave="artifact")
    
    # Nodo Servidor BD
    n_db = d.nodo("Servidor de Base de Datos", 1200, 400, 320, 300,
                  palabra_clave="executionEnvironment", detalle="Motor MariaDB 10.4+", color=GRIS)
    d.artefacto("Schema bd_smartbin.sql", 1230, 490, 260, 80, palabra_clave="artifact",
                detalle="Tablas InnoDB con claves foráneas")
    d.artefacto("InnoDB Storage Engine", 1230, 590, 260, 70, palabra_clave="executionEnvironment")
    
    # Enlaces de red
    d.arista(n_mov, n_srv, LINK, valor="«protocolo»\nHTTP / JSON\n(Wi-Fi / 4G LTE Puerto 80)")
    d.arista(n_iot, n_srv, LINK, valor="«protocolo»\nHTTP POST / JSON\n(Wi-Fi 802.11 b/g/n)")
    d.arista(n_srv, n_db, LINK, valor="«conexión»\nTCP/IP Socket\n(127.0.0.1:3306)")
    
    drawio = os.path.join(OUT_DIR, "07-despliegue.drawio")
    png = os.path.join(OUT_DIR, "07-despliegue.png")
    d.guardar(drawio, png)
    print("07 Despliegue generado.")

# ==============================================================================
# 08. PAQUETES (UML 12)
# ==============================================================================
def gen_08_paquetes():
    d = Diagrama("Paquetes", 1600, 1100)
    d.titulo("Diagrama de paquetes — Modularización y Dependencias de Código",
             "UML 2.5.1 cláusula 12 — Paquetes de la aplicación Android y del backend PHP")
    
    d.marco_diagrama("pkg Estructura modular del proyecto Smart Waste Bin", 40, 80, 1520, 980)
    
    # Paquete Raíz Android
    p_and = d.paquete("com.unicolombo.smartwastebin (Android)", 80, 140, 680, 860, color=AZUL, pestana=320)
    
    p_vistas = d.paquete("views", 120, 210, 260, 220, color=BLANCO,
                         miembros="• LoginActivity.java\n• MainActivity.java\n• DepositarActivity.java\n• PuntosActivity.java\n• PerfilActivity.java")
    
    p_ctrls  = d.paquete("controllers", 450, 210, 260, 220, color=BLANCO,
                         miembros="• AuthController.java\n• DepositoController.java\n• BilleteraController.java\n• LeaderboardController.java")
    
    p_models = d.paquete("models", 450, 490, 260, 200, color=BLANCO,
                         miembros="• Usuario.java\n• SmartBin.java\n• Residuo.java\n• Transaccion.java\n• Canje.java")
    
    p_net    = d.paquete("network", 120, 490, 260, 200, color=BLANCO,
                         miembros="• VolleySingleton.java\n• ApiEndpoints.java\n• JsonParser.java")
    
    p_utils  = d.paquete("utils", 280, 750, 280, 180, color=BLANCO,
                         miembros="• SessionManager.java\n• AppConstants.java\n• QRGenerator.java")
    
    # Paquete Raíz Backend
    p_back = d.paquete("Smart_waste_bin_6to_semestre (Backend)", 860, 140, 660, 860, color=VERDE, pestana=340)
    
    p_api   = d.paquete("api/", 900, 210, 270, 280, color=BLANCO,
                        miembros="• login.php\n• registro.php\n• depositar.php\n• get_puntos.php\n• canjear_puntos.php\n• get_bins.php\n• leaderboard.php")
    
    p_cfg   = d.paquete("config/", 1220, 210, 260, 160, color=BLANCO,
                        miembros="• conexion.php (PDO)\n• cors.php\n• security.php")
    
    p_views = d.paquete("views/", 900, 560, 270, 220, color=BLANCO,
                        miembros="• dashboard.php\n• mapa.php\n• usuarios.php\n• reportes.php")
    
    p_ast   = d.paquete("assets/", 1220, 560, 260, 220, color=BLANCO,
                        miembros="• css/glassmorphism.css\n• js/mapa_cartagena.js\n• img/background.jpg\n• img/mapa_cartagena.jpg")
    
    # Dependencias internas Android
    d.arista(p_vistas, p_ctrls, DEPENDENCIA, valor="«use»")
    d.arista(p_ctrls, p_models, DEPENDENCIA, valor="«use»")
    d.arista(p_ctrls, p_net, DEPENDENCIA, valor="«use»")
    d.arista(p_ctrls, p_utils, DEPENDENCIA, valor="«use»")
    d.arista(p_net, p_utils, DEPENDENCIA, valor="«use»")
    
    # Dependencia entre capas (Móvil usa API REST)
    d.arista(p_net, p_api, DEPENDENCIA, valor="«peticiones HTTP»")
    
    # Dependencias internas Backend
    d.arista(p_api, p_cfg, DEPENDENCIA, valor="«use»")
    d.arista(p_views, p_cfg, DEPENDENCIA, valor="«use»")
    d.arista(p_views, p_ast, DEPENDENCIA, valor="«renderiza»")
    
    drawio = os.path.join(OUT_DIR, "08-paquetes.drawio")
    png = os.path.join(OUT_DIR, "08-paquetes.png")
    d.guardar(drawio, png)
    print("08 Paquetes generado.")

# ==============================================================================
# 09. COMUNICACIÓN (UML 17.9)
# ==============================================================================
def gen_09_comunicacion():
    d = Diagrama("Comunicación", 1500, 1000)
    d.titulo("Diagrama de comunicación — Colaboración en Depósito de Residuos",
             "UML 2.5.1 cláusula 17.9 — Grafo de enlaces con mensajes numerados secuencialmente")
    
    d.marco_diagrama("comm Flujo de colaboración para procesar reciclaje", 40, 80, 1420, 880)
    
    # Objetos
    c_ciu  = d.actor("c : Ciudadano", 120, 220, tipo="humano")
    o_ui   = d.clase_simple("ui : DepositarActivity", 380, 220, 220, 60, color=AZUL, objeto=True)
    o_cli  = d.clase_simple("net : ApiClientService", 760, 220, 220, 60, color=AZUL, objeto=True)
    o_api  = d.clase_simple("router : ApiDepositar", 1140, 220, 220, 60, color=VERDE, objeto=True)
    
    o_bin  = d.clase_simple("bin : SmartBin", 1140, 480, 220, 60, color=AMARILLO, objeto=True)
    o_res  = d.clase_simple("res : Residuo", 760, 480, 220, 60, color=AZUL, objeto=True)
    o_cta  = d.clase_simple("cta : CuentaPuntos", 380, 480, 220, 60, color=VERDE, objeto=True)
    o_db   = d.clase_simple("db : MariaDB_PDO", 760, 740, 220, 60, color=GRIS, objeto=True)
    
    # Enlaces y mensajes
    # 1. Ciudadano -> UI
    d.arista(c_ciu, o_ui, ASOCIACION)
    d.mensaje_en_enlace((160, 250), (380, 250), "1: presionarBotonDepositar(tipo, peso)", lado=-1, t=0.5)
    
    # 2. UI -> ApiClient
    d.arista(o_ui, o_cli, ASOCIACION)
    d.mensaje_en_enlace((600, 250), (760, 250), "1.1: enviarPeticion(json)", lado=-1, t=0.5)
    
    # 3. ApiClient -> Router
    d.arista(o_cli, o_api, ASOCIACION)
    d.mensaje_en_enlace((980, 250), (1140, 250), "1.2: POST /depositar.php", lado=-1, t=0.5)
    
    # 4. Router -> SmartBin
    d.arista(o_api, o_bin, ASOCIACION)
    d.mensaje_en_enlace((1250, 280), (1250, 480), "1.3: verificarCapacidad(peso)", lado=1, t=0.5)
    
    # 5. SmartBin -> DB
    d.arista(o_bin, o_db, ASOCIACION)
    d.mensaje_en_enlace((1140, 520), (980, 740), "1.4: SELECT capacidad, nivel", lado=-1, t=0.5)
    
    # 6. Router -> Residuo
    d.arista(o_api, o_res, ASOCIACION)
    d.mensaje_en_enlace((1140, 280), (950, 480), "1.5: calcularPuntos()", lado=1, t=0.5)
    
    # 7. Residuo -> DB
    d.arista(o_res, o_db, ASOCIACION)
    d.mensaje_en_enlace((870, 540), (870, 740), "1.6: INSERT residuo & UPDATE bin", lado=1, t=0.5)
    
    # 8. Router -> CuentaPuntos
    d.arista(o_api, o_cta, ASOCIACION)
    d.mensaje_en_enlace((1140, 260), (550, 480), "2.0: acreditarPuntos(saldo_ganado)", lado=-1, t=0.6)
    
    # 9. CuentaPuntos -> DB
    d.arista(o_cta, o_db, ASOCIACION)
    d.mensaje_en_enlace((490, 540), (760, 740), "2.1: UPDATE saldo & INSERT transaccion", lado=1, t=0.5)
    
    drawio = os.path.join(OUT_DIR, "09-comunicacion.drawio")
    png = os.path.join(OUT_DIR, "09-comunicacion.png")
    d.guardar(drawio, png)
    print("09 Comunicación generado.")

# ==============================================================================
# 10. OBJETOS (UML 9.8)
# ==============================================================================
def gen_10_objetos():
    d = Diagrama("Objetos", 1500, 1000)
    d.titulo("Diagrama de objetos — Instantánea de Ejecución (Snapshot)",
             "UML 2.5.1 cláusula 9.8 — Instancias con nombres subrayados y valores reales de bd_smartbin")
    
    d.marco_diagrama("obj Instantánea de depósito del usuario 'juanki'", 40, 80, 1420, 880)
    
    # Instancias de objetos
    o_usr = d.clase("u1 : Usuario", 100, 180, 320,
                    atributos=[
                        "id_usuario = 3",
                        "nombre = 'Juan Camilo Puello'",
                        "correo = 'jpuello@unicolombo.edu.co'",
                        "usuario = 'juanki'",
                        "rol = 'Ciudadano'",
                        "qr_code = 'QR_USER_003.png'",
                        "fecha_registro = 2026-10-20"
                    ], objeto=True, color=AZUL)
    
    o_cta = d.clase("cp1 : CuentaPuntos", 580, 180, 320,
                    atributos=[
                        "id_cuenta = 3",
                        "saldo_actual = 135",
                        "fecha_actualizacion = 2026-10-22 10:15:02"
                    ], objeto=True, color=VERDE)
    
    o_trx = d.clase("t88 : TransaccionPuntos", 1060, 180, 320,
                    atributos=[
                        "id_transaccion = 88",
                        "tipo = 'ABONO_RECICLAJE'",
                        "cantidad = 25",
                        "fecha = 2026-10-22 10:15:01"
                    ], objeto=True, color=VERDE)
    
    o_res = d.clase("r104 : Residuo", 580, 560, 320,
                    atributos=[
                        "id_residuo = 104",
                        "tipo = PLASTICO",
                        "peso = 2.5",
                        "fecha = 2026-10-22 10:15:00"
                    ], objeto=True, color=AZUL)
    
    o_bin = d.clase("b2 : SmartBin", 100, 560, 340,
                    atributos=[
                        "id_bin = 2",
                        "ubicacion = 'Hotel Casa Don Luis (Centro)'",
                        "capacidad_maxima = 50.0",
                        "nivel_actual = 12.5",
                        "estado = 'Activo'",
                        "latitud = 10.4236",
                        "longitud = -75.5492"
                    ], objeto=True, color=AMARILLO)
    
    # Enlaces de instancia
    d.arista(o_usr, o_cta, ASOCIACION, etiquetas=[(0.0, "titular_cuenta", "bottom")])
    d.arista(o_cta, o_trx, ASOCIACION, etiquetas=[(0.0, "auditoria_movimiento", "bottom")])
    d.arista(o_usr, o_res, ASOCIACION, etiquetas=[(0.0, "autor_deposito", "left")])
    d.arista(o_bin, o_res, ASOCIACION, etiquetas=[(0.0, "contenedor_receptor", "bottom")])
    
    drawio = os.path.join(OUT_DIR, "10-objetos.drawio")
    png = os.path.join(OUT_DIR, "10-objetos.png")
    d.guardar(drawio, png)
    print("10 Objetos generado.")

if __name__ == "__main__":
    print("Iniciando generación de los 10 diagramas en draw.io...")
    gen_01_casos_de_uso()
    gen_02_clases()
    gen_03_secuencia()
    gen_04_actividades()
    gen_05_estados()
    gen_06_componentes()
    gen_07_despliegue()
    gen_08_paquetes()
    gen_09_comunicacion()
    gen_10_objetos()
    print("¡Todos los 10 diagramas UML fueron generados y exportados a PNG exitosamente!")
