# SMART WASTE BIN (SWB) — ESPECIFICACIÓN Y DISEÑO DE DIAGRAMAS UML DE ARQUITECTURA
**Proyecto de Aula — Ingeniería de Sistemas — 6to Semestre**  
**Institución:** Corporación Universitaria Rafael Núñez / UNICOLOMBO  
**Autores del Proyecto:** Klever Polo Bolívar & Juan Camilo Puello  
**Materia:** Ingeniería de Software / Electiva de Programación  
**Versión del Documento:** 2.0 (Consolidado Web, Móvil e IoT)

---

## INTRODUCCIÓN Y ALCANCE DE LA ARQUITECTURA

El sistema **Smart Waste Bin (SWB)** es una plataforma ciberfísica e integral orientada a la recolección inteligente de residuos y reciclaje gamificado en entornos urbanos y universitarios. Integra:
1. **Estaciones Físicas IoT:** Contenedores sensorizados con celdas de carga y ultrasonido para medir peso y volumetría en tiempo real, con límite operativo de 50.0 kg.
2. **Aplicación Móvil (Android Java):** Cliente para el ciudadano que permite autenticación, escaneo QR de estaciones, registro de depósitos, consulta de billetera ecológica, visualización de leaderboard y canje de puntos a dinero en efectivo (Nequi/Daviplata a razón de 10 pts = \$100 COP).
3. **Plataforma Web Administrativa (PHP / Apache / Glassmorphism UI):** Panel de telemetría y logística con geolocalización de contenedores en Cartagena de Indias, monitoreo de estados (Activo / Bloqueado) y gestión de usuarios.
4. **Capa de Persistencia (MariaDB InnoDB):** Base de datos relacional transaccional (`bd_smartbin`) con control de concurrencia y auditoría de puntos.

A continuación se presentan formalmente los **10 diagramas de arquitectura UML** solicitados para la entrega del proyecto de aula.

---

## 1. DIAGRAMAS DE CASOS DE USO (UML USE CASE DIAGRAM)

### 1.1 Diagrama de Casos de Uso General del Sistema
Muestra la interacción global de los tres actores principales:
* **Ciudadano / Usuario Móvil:** Usuario registrado que deposita material, acumula y canjea puntos.
* **Administrador / Operador de Aseo:** Gestiona el parque de SmartBins, usuarios y logística de vaciado.
* **Sistema IoT / Sensores de Estación:** Actor tecnológico que captura métricas físicas y notifica saturación.

```mermaid
flowchart LR
    %% Actores
    subgraph Actores["Actores del Sistema"]
        C["fa:fa-user Ciudadano"]
        A["fa:fa-user-tie Administrador"]
        S["fa:fa-microchip Sensor IoT / Bin"]
    end

    %% Casos de Uso
    subgraph Sistema["Sistema Smart Waste Bin (SWB)"]
        UC1(["UC-01: Autenticar Usuario / Login"])
        UC2(["UC-02: Registrar Nueva Cuenta"])
        UC3(["UC-03: Escanear / Identificar SmartBin"])
        UC4(["UC-04: Depositar Residuo"])
        UC5(["UC-05: Pesar y Validar Residuo"])
        UC6(["UC-06: Liquidar y Acreditar Puntos"])
        UC7(["UC-07: Consultar Billetera y Ranking"])
        UC8(["UC-08: Canjear Puntos por Dinero COP"])
        UC9(["UC-09: Monitorear Telemetría y Bins en Mapa"])
        UC10(["UC-10: Bloquear Bin por Saturación (>=50kg)"])
        UC11(["UC-11: Registrar Vaciado y Mantenimiento"])
        UC12(["UC-12: Gestionar Usuarios y Reportes"])
    end

    %% Relaciones de actores
    C --> UC1
    C --> UC2
    C --> UC3
    C --> UC4
    C --> UC7
    C --> UC8

    A --> UC1
    A --> UC9
    A --> UC11
    A --> UC12

    S --> UC5
    S --> UC10

    %% Inclusiones y extensiones
    UC4 -.->|«include»| UC5
    UC5 -.->|«include»| UC6
    UC5 -.->|«extend»| UC10
```

---

### 1.2 Diagramas de Casos de Uso por Partes (Subsistemas)

#### Subsistema A: Autenticación y Perfil de Usuario
```mermaid
flowchart LR
    User["Ciudadano / Administrador"]

    subgraph Sub_Auth["Subsistema de Gestión de Acceso y Perfiles"]
        UC_Reg(["UC-02: Registro de Usuario"])
        UC_Log(["UC-01: Inicio de Sesión"])
        UC_Prof(["UC-13: Ver y Modificar Perfil"])
        UC_QR(["UC-14: Generar y Descargar Carné QR"])
        UC_Val(["Validar Credenciales"])
    end

    User --> UC_Reg
    User --> UC_Log
    User --> UC_Prof
    User --> UC_QR

    UC_Log -.->|«include»| UC_Val
    UC_Reg -.->|«include»| UC_QR
```

#### Subsistema B: Depósito y Reciclaje IoT
```mermaid
flowchart LR
    Ciudadano["fa:fa-user Ciudadano"]
    Sensor["fa:fa-microchip Estación IoT"]

    subgraph Sub_Reciclaje["Subsistema de Depósito y Pesaje"]
        UC_Select(["Seleccionar Tipo de Residuo"])
        UC_Medir(["Capturar Peso con Celda HX711"])
        UC_Cap(["Verificar Capacidad Disponible (<50kg)"])
        UC_Alerta(["Emitir Alerta de Saturación"])
        UC_Trans(["Acreditar Puntos al Usuario"])
    end

    Ciudadano --> UC_Select
    Sensor --> UC_Medir
    UC_Select --> UC_Medir
    UC_Medir -.->|«include»| UC_Cap
    UC_Cap -.->|«extend»| UC_Alerta
    UC_Cap -.->|«include»| UC_Trans
```

#### Subsistema C: Gamificación, Puntos y Canjes
```mermaid
flowchart LR
    Ciudadano["fa:fa-user Ciudadano"]

    subgraph Sub_Puntos["Subsistema de Monedero y Gamificación"]
        UC_Puntos(["Consultar Saldo Actual"])
        UC_Hist(["Consultar Historial de Transacciones"])
        UC_Rank(["Ver Tabla de Clasificación (Leaderboard)"])
        UC_Canje(["Solicitar Canje a Nequi / Daviplata"])
        UC_Reglas(["Aplicar Regla de Conversión: 10 pts = $100 COP"])
    end

    Ciudadano --> UC_Puntos
    Ciudadano --> UC_Hist
    Ciudadano --> UC_Rank
    Ciudadano --> UC_Canje

    UC_Canje -.->|«include»| UC_Reglas
```

#### Subsistema D: Supervisión, Telemetría y Operación de Aseo (Admin)
```mermaid
flowchart LR
    Admin["fa:fa-user-shield Administrador / Operador"]

    subgraph Sub_Admin["Subsistema de Telemetría y Logística"]
        UC_Mapa(["Visualizar Mapa Interactivo de Cartagena"])
        UC_Estado(["Consultar Estado y Porcentaje de Bins"])
        UC_Vaciar(["Registrar Vaciado de Contenedor"])
        UC_Reporte(["Exportar Reportes de Recolección"])
    end

    Admin --> UC_Mapa
    Admin --> UC_Estado
    Admin --> UC_Vaciar
    Admin --> UC_Reporte
```

---

## 2. DIAGRAMA DE CLASES (UML CLASS DIAGRAM)

Modela las entidades de dominio, los controladores de la aplicación móvil y los servicios backend que sustentan la persistencia en `bd_smartbin`.

```mermaid
classDiagram
    direction TB

    class Usuario {
        -int id_usuario
        -String nombre
        -String correo
        -String usuario
        -String password
        -String rol
        -String qr_code
        -DateTime fecha_registro
        +registrar() bool
        +autenticar() bool
        +obtenerPerfil() Usuario
        +actualizarPerfil() bool
    }

    class CuentaPuntos {
        -int id_cuenta
        -int id_usuario
        -int saldo_actual
        -DateTime fecha_actualizacion
        +consultarSaldo() int
        +acreditarPuntos(int cantidad) bool
        +debitarPuntos(int cantidad) bool
        +obtenerRangoEcológico() String
    }

    class TransaccionPuntos {
        -int id_transaccion
        -int id_cuenta
        -String tipo
        -int cantidad
        -DateTime fecha
        +crearTransaccion() bool
        +listarPorCuenta(int id_cuenta) List
    }

    class SmartBin {
        -int id_bin
        -String ubicacion
        -float capacidad_maxima
        -float nivel_actual
        -String estado
        -float latitud
        -float longitud
        +verificarCapacidad(float peso) bool
        +incrementarNivel(float peso) void
        +bloquearContenedor() void
        +vaciarContenedor() void
        +obtenerPorcentajeLlenado() float
    }

    class Residuo {
        -int id_residuo
        -int id_usuario
        -int id_bin
        -TipoResiduo tipo
        -float peso
        -DateTime fecha
        +calcularPuntos() int
        +registrarDeposito() bool
    }

    class TipoResiduo {
        <<enumeration>>
        PLASTICO = 10 pts/kg
        VIDRIO = 15 pts/kg
        ELECTRONICO = 50 pts/kg
        PAPEL_CARTON = 5 pts/kg
    }

    class CanjeRecompensa {
        -int id_canje
        -int id_usuario
        -int puntos_canjeados
        -float valor_cop
        -String telefono_destino
        -String plataforma
        -DateTime fecha
        +validarNumero(String tel) bool
        +procesarCanje() bool
    }

    class ApiClientService {
        -String baseUrl
        +postLogin(String user, String pass)
        +postDepositar(int idU, int idB, String tipo, float peso)
        +getSaldoPuntos(int idU)
        +postCanje(int idU, int pts, String tel)
    }

    %% Relaciones
    Usuario "1" *-- "1" CuentaPuntos : posee
    CuentaPuntos "1" o-- "0..*" TransaccionPuntos : registra
    Usuario "1" --> "0..*" Residuo : deposita
    SmartBin "1" --> "0..*" Residuo : recepciona
    Residuo ..> TipoResiduo : clasificado en
    Usuario "1" --> "0..*" CanjeRecompensa : solicita
    ApiClientService ..> Usuario : sincroniza
    ApiClientService ..> Residuo : despacha
```

---

## 3. DIAGRAMA DE SECUENCIA (UML SEQUENCE DIAGRAM)

Detalla la interacción dinámica paso a paso durante el evento crítico del sistema: **Registro de depósito de residuo con pesaje, validación de capacidad en SmartBin, actualización de saldo y bloqueo preventivo**.

```mermaid
sequenceDiagram
    autonumber
    actor C as Ciudadano (App Móvil)
    participant UI as DepositarActivity
    participant API as ApiClient / depositar.php
    participant BD as MariaDB (bd_smartbin)
    actor IoT as Sensor / SmartBin

    C ->> UI: 1. Selecciona contenedor, tipo de residuo y peso
    UI ->> API: 2. POST /api/depositar.php {id_usuario, id_bin, tipo, peso}
    
    activate API
    API ->> BD: 3. SELECT capacidad_maxima, nivel_actual, estado FROM smartbin WHERE id_bin = ?
    BD -->> API: 4. Retorna {cap_max: 50.0, nivel: 48.0, estado: 'Activo'}
    
    alt Capacidad excedida (nivel + peso > 50.0)
        API -->> UI: 5a. HTTP 400 {success: false, error: "Capacidad excedida"}
        UI -->> C: 6a. Muestra alerta: "Contenedor saturado. Depósito denegado."
    else Capacidad disponible
        API ->> API: 5b. Calcular puntos: round(peso * factor) [ej: 2.5kg PLASTICO = 25 pts]
        API ->> BD: 6b. INICIAR TRANSACCIÓN (BEGIN)
        API ->> BD: 7b. INSERT INTO residuo (id_usuario, id_bin, tipo, peso, fecha)
        API ->> BD: 8b. UPDATE smartbin SET nivel_actual = nivel_actual + peso WHERE id_bin = ?
        
        opt Si nuevo nivel >= 50.0 kg
            API ->> BD: 9b. UPDATE smartbin SET estado = 'Bloqueado' WHERE id_bin = ?
            API ->> IoT: 10b. Activar actuador electromecánico / LED rojo (Bloqueo físico)
        end
        
        API ->> BD: 11b. UPDATE cuenta_puntos SET saldo_actual = saldo_actual + puntos
        API ->> BD: 12b. INSERT INTO transaccion_puntos (id_cuenta, tipo, cantidad)
        API ->> BD: 13b. COMMIT TRANSACCIÓN
        
        API -->> UI: 14b. HTTP 200 {success: true, puntos_ganados: 25, nuevo_saldo: 135}
        deactivate API
        
        UI -->> C: 15b. Notificación de éxito: "+25 puntos acreditados a tu cuenta"
    end
```

---

## 4. DIAGRAMA DE ACTIVIDADES (UML ACTIVITY DIAGRAM)

Muestra el flujo de control procedimental para el ciclo completo de reciclaje, desde la captura del material hasta la emisión de recompensas y evaluación de saturación.

```mermaid
flowchart TD
    Start((Inicio)) --> A1[Ciudadano se acerca al Smart Waste Bin]
    A1 --> A2[Abrir App Móvil o Interfaz de la Estación]
    A2 --> A3{¿Usuario Autenticado?}
    
    A3 -- No --> A4[Iniciar Sesión o Escanear Carné QR]
    A4 --> A5[Identificar y Seleccionar SmartBin]
    A3 -- Sí --> A5
    
    A5 --> A6[Seleccionar Tipo de Residuo en Sistema]
    A6 --> A7[Colocar Residuo en Tolva de Depósito]
    A7 --> A8[Sensor / Celda HX711 realiza pesaje preciso]
    
    A8 --> A9{¿Peso > 0.05 kg?}
    A9 -- No --> A10[Error: Peso insuficiente o no detectado]
    A10 --> EndFailure((Fin con Error))
    
    A9 -- Sí --> A11[Consultar nivel actual y capacidad del Bin]
    A11 --> A12{¿Nivel + Peso <= 50.0 kg?}
    
    A12 -- No --> A13[Alerta: El contenedor se saturaría con este residuo]
    A13 --> A14[Rechazar depósito y notificar a la empresa de aseo]
    A14 --> EndSaturado((Fin - Rechazado))
    
    A12 -- Sí --> A15[Recibir material e ingresar a almacenamiento interno]
    A15 --> A16[Calcular Puntos = Redondear: Peso x Factor]
    A16 --> A17[Registrar Residuo en Base de Datos]
    A17 --> A18[Actualizar nivel_actual del SmartBin]
    A18 --> A19[Acreditar Puntos en cuenta_puntos del Ciudadano]
    A19 --> A20[Registrar Auditoría en transaccion_puntos]
    
    A20 --> A21{¿Nivel_actual >= 50.0 kg?}
    A21 -- Sí --> A22[Cambiar estado del Bin a 'Bloqueado']
    A22 --> A23[Mostrar Alerta de Bloqueo en Dashboard Web Admin]
    A23 --> A24[Generar Comprobante en Pantalla Móvil]
    A21 -- No --> A24
    
    A24 --> EndSuccess((Fin Exitoso))
```

---

## 5. DIAGRAMA DE ESTADOS (STATE MACHINE DIAGRAM)

Representa los estados finitos y transiciones por los que atraviesa una estación física `SmartBin` durante su operación en campo.

```mermaid
stateDiagram-v2
    [*] --> Vacio_Disponible : Inicialización / Post-Vaciado

    Vacio_Disponible --> Operativo_CargaMedia : depositoRegistrado [0 < nivel < 40 kg]
    
    Operativo_CargaMedia --> Operativo_CargaMedia : depositoRegistrado [nivel < 40 kg]
    Operativo_CargaMedia --> Alerta_CasiLleno : depositoRegistrado [40 kg <= nivel < 50 kg]
    
    Alerta_CasiLleno --> Alerta_CasiLleno : depositoRegistrado [nivel < 50 kg]
    Alerta_CasiLleno --> Bloqueado_Saturado : depositoRegistrado [nivel >= 50 kg]
    Alerta_CasiLleno --> Bloqueado_Preventivo : intentoDepositoExcedeCapacidad
    
    Bloqueado_Saturado --> En_Mantenimiento : operadorAseoLlegaConLlave / QR
    Bloqueado_Preventivo --> En_Mantenimiento : operadorAseoLlegaConLlave / QR
    
    state En_Mantenimiento {
        [*] --> AperturaTolva
        AperturaTolva --> ExtraccionBolsaResiduos
        ExtraccionBolsaResiduos --> CalibracionTaraSensor
        CalibracionTaraSensor --> [*]
    }
    
    En_Mantenimiento --> Vacio_Disponible : confirmarVaciadoAdmin [nivel = 0.0 kg & estado = 'Activo']
    
    Vacio_Disponible --> Fuera_De_Servicio : fallaConectividad / sensorDañado
    Operativo_CargaMedia --> Fuera_De_Servicio : fallaConectividad / sensorDañado
    Fuera_De_Servicio --> En_Mantenimiento : ordenTrabajoTecnico
```

---

## 6. DIAGRAMA DE COMPONENTES (UML COMPONENT DIAGRAM)

Describe la organización y dependencias de los módulos de software en las tres capas: Cliente Móvil, Servidor Backend y Persistencia.

```mermaid
flowchart TD
    subgraph CapaCliente["Capa de Presentación / Clientes"]
        subgraph AppAndroid["Aplicación Android (Java)"]
            UI_Views["Vistas XML / Activities"]
            Volley_Service["Servicio HTTP (Volley / REST)"]
            Session_Store["Gestor de Sesión SharedPreferences"]
            QR_Scanner["Módulo Escáner ZXing / QR"]
        end

        subgraph WebAdmin["Dashboard Administrativo Web"]
            Glass_UI["Interfaz Glassmorphism (HTML5/CSS3)"]
            JS_Map["Módulo Mapa Interactivo Cartagena"]
            Chart_Engine["Visualizador Gráfico Estadístico"]
        end
    end

    subgraph CapaBackend["Capa Lógica de Negocio / Servidor Web (Apache + PHP 8)"]
        Router["Enrutador de Peticiones HTTP / CORS"]
        
        subgraph ModulosAPI["Módulos de la API REST"]
            Auth_Module["Módulo de Autenticación (login/registro)"]
            Deposit_Module["Módulo de Depósito (depositar.php)"]
            Points_Module["Módulo de Billetera (puntos/canjes)"]
            Bins_Module["Módulo de Telemetría (smartbins.php)"]
        end
        
        Engine_Rules["Motor de Reglas de Conversión de Puntos"]
        PDO_Driver["Capa de Abstracción PDO MariaDB"]
    end

    subgraph CapaPersistencia["Capa de Persistencia de Datos"]
        BD[("Base de Datos MariaDB\n(bd_smartbin / Motor InnoDB)")]
    end

    %% Conexiones
    UI_Views --> Volley_Service
    Volley_Service --> Router
    Glass_UI --> Router
    JS_Map --> Router

    Router --> Auth_Module
    Router --> Deposit_Module
    Router --> Points_Module
    Router --> Bins_Module

    Deposit_Module --> Engine_Rules
    Points_Module --> Engine_Rules

    Auth_Module --> PDO_Driver
    Deposit_Module --> PDO_Driver
    Points_Module --> PDO_Driver
    Bins_Module --> PDO_Driver

    PDO_Driver --> BD
```

---

## 7. DIAGRAMA DE DESPLIEGUE (UML DEPLOYMENT DIAGRAM)

Especifica la topología de hardware, nodos físicos y los canales de comunicación de red utilizados para el despliegue del ecosistema Smart Waste Bin.

```mermaid
flowchart TD
    subgraph NodoMovil["Nodo: Smartphone Android (Cliente)"]
        subgraph SO_Android["SO: Android 8.0 Oreo o Superior"]
            App_APK["SmartWasteBinApp.apk\n«artifact»\nJava Runtime"]
        end
    end

    subgraph NodoIoT["Nodo: Estación Contenedor SmartBin (IoT)"]
        subgraph HW_IoT["Hardware Embebido"]
            MCU["Microcontrolador ESP32 / Arduino"]
            Sensor_Peso["Celda de Carga + Módulo HX711"]
            Sensor_Nivel["Sensor Ultrasonido HC-SR04"]
            Actuador_Luz["LED Indicador / Servo Bloqueo"]
        end
    end

    subgraph NodoServidor["Nodo: Servidor Local / Cloud (XAMPP Environment)"]
        subgraph Servidor_Web["Servidor HTTP Apache 2.4"]
            Runtime_PHP["Intérprete PHP 8.x"]
            API_Artifacts["Scripts API Backend / Endpoints REST\n«artifact»"]
            Web_Artifacts["Portal Web Glassmorphism (Assets/HTML)\n«artifact»"]
        end
        
        subgraph Servidor_BD["Servidor de Base de Datos"]
            MariaDB_Service["Motor MariaDB 10.4+\nPuerto TCP 3306"]
            Schema_Smartbin["bd_smartbin.sql\n«schema InnoDB»"]
        end
    end

    subgraph NodoAdmin["Nodo: Estación de Monitoreo / PC Operador"]
        subgraph Navegador["Navegador Web Moderno"]
            Web_Client["Google Chrome / Microsoft Edge"]
        end
    end

    %% Protocolos y Enlaces
    NodoMovil <-->|HTTP / JSON\nPuerto 80 / Wi-Fi / LTE| Servidor_Web
    NodoIoT <-->|Serial UART / HTTP POST\nWi-Fi 2.4GHz| Servidor_Web
    NodoAdmin <-->|HTTP / WebSockets\nPuerto 80 / LAN| Servidor_Web
    Runtime_PHP <-->|Conexión Local TCP/IP\nsocket 127.0.0.1:3306| MariaDB_Service
    MariaDB_Service --> Schema_Smartbin
```

---

## 8. DIAGRAMA DE PAQUETES (UML PACKAGE DIAGRAM)

Ilustra la modularización, namespaces y estructura de carpetas tanto del cliente móvil Android como del servidor web PHP.

```mermaid
flowchart TB
    subgraph PKG_Android["Paquete Móvil: com.unicolombo.smartwastebin"]
        subgraph PKG_Vistas["views"]
            V1["LoginActivity"]
            V2["MainActivity"]
            V3["DepositarActivity"]
            V4["PuntosActivity"]
            V5["PerfilActivity"]
        end

        subgraph PKG_Controladores["controllers"]
            C1["AuthController"]
            C2["DepositoController"]
            C3["PuntosController"]
        end

        subgraph PKG_Modelos["models"]
            M1["Usuario"]
            M2["SmartBin"]
            M3["Residuo"]
            M4["Transaccion"]
        end

        subgraph PKG_Network["network"]
            N1["VolleySingleton"]
            N2["ApiEndpoints"]
            N3["JsonParser"]
        end

        subgraph PKG_Utils["utils"]
            U1["SessionManager"]
            U2["Constants"]
            U3["Formatters"]
        end
    end

    subgraph PKG_Backend["Paquete Backend: /Smart_waste_bin_6to_semestre"]
        subgraph PKG_Config["config/"]
            CFG1["conexion.php"]
            CFG2["cors.php"]
        end

        subgraph PKG_API["api/"]
            A1["login.php"]
            A2["registro.php"]
            A3["depositar.php"]
            A4["get_puntos.php"]
            A5["canjear_puntos.php"]
            A6["get_bins.php"]
            A7["leaderboard.php"]
        end

        subgraph PKG_ViewsWeb["views/"]
            VW1["dashboard.php"]
            VW2["mapa.php"]
            VW3["usuarios.php"]
        end

        subgraph PKG_Assets["assets/"]
            AST1["css/glassmorphism.css"]
            AST2["js/mapa_cartagena.js"]
            AST3["img/"]
        end
    end

    %% Dependencias entre paquetes
    PKG_Vistas ..> PKG_Controladores
    PKG_Controladores ..> PKG_Modelos
    PKG_Controladores ..> PKG_Network
    PKG_Network ..> PKG_Utils
    
    PKG_Network ..> PKG_API : Peticiones HTTP
    PKG_ViewsWeb ..> PKG_Assets
    PKG_API ..> PKG_Config
    PKG_ViewsWeb ..> PKG_Config
```

---

## 9. DIAGRAMA DE COMUNICACIÓN (UML COMMUNICATION DIAGRAM)

Presenta la colaboración entre objetos en tiempo de ejecución para el escenario de **Depósito y Notificación de Puntos**, haciendo énfasis en el orden secuencial de los enlaces y mensajes transmitidos.

```mermaid
flowchart TD
    Ciudadano(["1: Ciudadano"])
    UI[":DepositarActivity"]
    Client[":ApiClientService"]
    API[":ApiDepositarRouter"]
    BinObj[":SmartBinEntity"]
    ResObj[":ResiduoEntity"]
    PtosObj[":CuentaPuntosEntity"]
    DB[":MariaDB_PDO"]

    %% Mensajes numerados
    Ciudadano -->|"1.0: presionarBotonDepositar(tipo, peso)"| UI
    UI -->|"1.1: enviarPeticion(idUsuario, idBin, tipo, peso)"| Client
    Client -->|"1.2: POST /api/depositar.php"| API
    
    API -->|"1.3: consultarCapacidad(idBin)"| BinObj
    BinObj -->|"1.4: SELECT nivel_actual, cap_max"| DB
    DB -->>|"1.5: retorna métricas"| BinObj
    
    API -->|"1.6: instanciar(tipo, peso)"| ResObj
    ResObj -->|"1.7: calcularPuntos()"| ResObj
    
    API -->|"1.8: INSERT INTO residuo"| DB
    API -->|"1.9: UPDATE smartbin SET nivel_actual"| DB
    API -->|"2.0: acreditarPuntos(saldo_ganado)"| PtosObj
    PtosObj -->|"2.1: UPDATE cuenta_puntos"| DB
    PtosObj -->|"2.2: INSERT INTO transaccion_puntos"| DB
    
    API -->>|"2.3: {success: true, puntos_obtenidos: 25}"| Client
    Client -->>|"2.4: callbackExito()"| UI
    UI -->>|"2.5: notificarUsuario('Depósito acreditado!')"| Ciudadano
```

---

## 10. DIAGRAMA DE OBJETOS (UML OBJECT DIAGRAM)

Muestra una instantánea concreta en tiempo de ejecución (Snapshot) con los objetos instanciados, sus atributos poblados con datos verídicos de la base de datos `bd_smartbin` y los enlaces activos entre ellos.

```mermaid
classDiagram
    direction LR

    class Usuario_Instancia {
        <<object>>
        u1: Usuario
        ---
        id_usuario = 3
        nombre = "Juan Camilo Puello"
        correo = "jpuello@unicolombo.edu.co"
        usuario = "juanki"
        rol = "Ciudadano"
        qr_code = "QR_USER_003.png"
        fecha_registro = "2026-10-20 09:30:00"
    }

    class CuentaPuntos_Instancia {
        <<object>>
        cp1: CuentaPuntos
        ---
        id_cuenta = 3
        id_usuario = 3
        saldo_actual = 135
        fecha_actualizacion = "2026-10-22 10:15:02"
    }

    class SmartBin_Instancia {
        <<object>>
        b2: SmartBin
        ---
        id_bin = 2
        ubicacion = "Hotel Casa Don Luis (Centro)"
        capacidad_maxima = 50.0
        nivel_actual = 12.5
        estado = "Activo"
        latitud = 10.4236
        longitud = -75.5492
    }

    class Residuo_Instancia {
        <<object>>
        r104: Residuo
        ---
        id_residuo = 104
        id_usuario = 3
        id_bin = 2
        tipo = "PLASTICO"
        peso = 2.5
        fecha = "2026-10-22 10:15:00"
    }

    class TransaccionPuntos_Instancia {
        <<object>>
        t88: TransaccionPuntos
        ---
        id_transaccion = 88
        id_cuenta = 3
        tipo = "ABONO_RECICLAJE"
        cantidad = 25
        fecha = "2026-10-22 10:15:01"
    }

    %% Enlaces entre instancias de objetos
    Usuario_Instancia "1" -- "1" CuentaPuntos_Instancia : titular
    CuentaPuntos_Instancia "1" -- "1" TransaccionPuntos_Instancia : audita
    Usuario_Instancia "1" -- "1" Residuo_Instancia : autor_deposito
    SmartBin_Instancia "1" -- "1" Residuo_Instancia : contenedor_receptor
```

---

## 11. MODELO RELACIONAL (PERFIL DE DATOS DE AMBLER)

Modela el esquema físico de la base de datos `bd_smartbin` en MariaDB (motor InnoDB), especificando claves primarias `«PK»`, foráneas `«FK»` y únicas `«UK»`, tipos de datos e integridad referencial en cascada.

```mermaid
erDiagram
    USUARIO ||--|| CUENTA_PUNTOS : "posee (1:1 ON DELETE CASCADE)"
    CUENTA_PUNTOS ||--o{ TRANSACCION_PUNTOS : "registra (1:N ON DELETE CASCADE)"
    USUARIO ||--o{ RESIDUO : "deposita (1:N ON DELETE CASCADE)"
    SMARTBIN ||--o{ RESIDUO : "recepciona (1:N ON DELETE RESTRICT)"

    USUARIO {
        int id_usuario PK
        string correo UK
        string usuario UK
        string nombre
        string rol
        string qr_code
        datetime fecha_registro
    }

    CUENTA_PUNTOS {
        int id_cuenta PK
        int id_usuario FK
        int saldo_actual
        datetime fecha_actualizacion
    }

    TRANSACCION_PUNTOS {
        int id_transaccion PK
        int id_cuenta FK
        string tipo
        int cantidad
        datetime fecha
    }

    SMARTBIN {
        int id_bin PK
        string ubicacion
        float capacidad_maxima
        float nivel_actual
        string estado
    }

    RESIDUO {
        int id_residuo PK
        int id_usuario FK
        int id_bin FK
        string tipo
        float peso
        datetime fecha
    }
```

---

## 12. MODELO ENTIDAD-RELACIÓN (DER EN NOTACIÓN DE CHEN)

Modelo conceptual de datos conforme a la notación de Chen: entidades (rectángulos), relaciones (rombos), atributos (óvalos con PK subrayada) y participación total (línea doble).

```
[USUARIO] === (posee) === [CUENTA_PUNTOS] === (registra) === [TRANSACCION_PUNTOS]
   ||                                                               
(deposita)                                                          
   ||                                                               
[RESIDUO] === (recepciona) === [SMARTBIN]
```

* **Participación Total (Línea Doble):** Toda `CUENTA_PUNTOS` depende de la existencia de un `USUARIO`. Todo `RESIDUO` pertenece obligatoriamente a un `USUARIO` y a una estación `SMARTBIN`.
* **Cardinalidades:**
  * `USUARIO` (1, parcial) --- posee --- (1, total) `CUENTA_PUNTOS`
  * `CUENTA_PUNTOS` (1, parcial) --- registra --- (N, total) `TRANSACCION_PUNTOS`
  * `USUARIO` (1, parcial) --- deposita --- (N, total) `RESIDUO`
  * `SMARTBIN` (1, parcial) --- recepciona --- (N, total) `RESIDUO`

---

## TABLA RESUMEN DE TRAZABILIDAD Y COBERTURA TÉCNICA

| No. | Tipo de Modelo / Diagrama | Propósito Arquitectónico en Smart Waste Bin | Componente Clave Representado |
|:---:|:---|:---|:---|
| **1** | **Casos de Uso** | Requisitos funcionales de Ciudadanos, Administradores y Sensores IoT. | App Móvil, Panel Web y Sensores. |
| **2** | **Clases** | Modelo de dominio orientado a objetos y persistencia en MariaDB. | Clases de negocio, reglas de cálculo y enums. |
| **3** | **Secuencia** | Ciclo de vida temporal del depósito de material y acreditación de puntos. | API REST `depositar.php` y base de datos. |
| **4** | **Actividades** | Algoritmo de validación de pesaje, capacidad y ramas de desborde/bloqueo. | Decisión de saturación (>= 50kg). |
| **5** | **Estados** | Máquina de estados de la papelera inteligente física (SmartBin). | Transición: Disponible $\rightarrow$ Lleno $\rightarrow$ Bloqueado $\rightarrow$ Vaciado. |
| **6** | **Componentes** | Interfaces provistas (○) y requeridas (Ɑ) entre cliente, API y BD. | Volley, Módulos API, Motor de Reglas y PDO. |
| **7** | **Despliegue** | Topología física de hardware, red WiFi/LTE y servidor Apache/MariaDB. | Smartphone, Tarjeta IoT, Servidor XAMPP. |
| **8** | **Paquetes** | Organización de paquetes Java Android y árbol de directorios PHP. | `com.unicolombo.smartwastebin` y `Smart_waste_bin_6to_semestre/`. |
| **9** | **Comunicación** | Interacción sincrónica entre objetos con numeración formal de mensajes. | Transmisión y confirmación de eventos de depósito. |
| **10** | **Objetos** | Instantánea (snapshot) de memoria con datos reales de la BD del proyecto. | Usuario `juanki`, SmartBin `Hotel Don Luis`, 25 puntos. |
| **11** | **Modelo Relacional** | Esquema físico de tablas, claves `«PK»`, `«FK»`, `«UK»` y reglas ACID. | Tablas InnoDB normalizadas en `bd_smartbin`. |
| **12** | **Modelo DER (Chen)** | Modelo conceptual con entidades, relaciones, atributos y participación total. | Entidades conceptuales y restricciones de existencia. |
