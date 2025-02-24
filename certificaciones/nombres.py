import random

def generar_usuario(inicio_cedula, cedulas_usadas):
    nombres = ['Ana', 'Sofía', 'Camila', 'Valentina', 'Isabella', 'Mateo', 'Santiago', 'Lucas', 'Benjamín', 'Martín']
    apellidos = ['Fernández', 'González', 'López', 'Rodríguez', 'Pérez', 'Martínez', 'García', 'Sánchez', 'Díaz', 'Romero']
    dominios = ['gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com', 'live.com']

    nombre = random.choice(nombres)
    apellido = random.choice(apellidos)

    # Generar cédula única
    while True:
        cedula = str(inicio_cedula).zfill(8)
        if cedula not in cedulas_usadas:
            cedulas_usadas.add(cedula)
            break
        inicio_cedula += 1

    # Generar correo único
    while True:
        correo = f"{nombre.lower()}{random.randint(1, 999)}.{apellido.lower()}@{random.choice(dominios)}"
        if correo not in correos_usados:
            correos_usados.add(correo)
            break

    contrasena = "contraseña"

    return nombre, apellido, correo, contrasena, cedula

# Datos iniciales
inicio_cedula = 100
cedulas_usadas = set()
correos_usados = set()

# Generar 63 usuarios y mostrarlos sin comillas
for i in range(63):
    nombre, apellido, correo, contrasena, cedula = generar_usuario(inicio_cedula, cedulas_usadas)
    print(nombre, apellido, correo, contrasena, cedula)
    inicio_cedula += 1