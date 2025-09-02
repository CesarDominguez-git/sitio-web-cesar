import mysql.connector
import matplotlib.pyplot as plt

# Conexión a MySQL
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="TEC"
)

cursor = db.cursor()
# Consulta: total de tareas por usuario
cursor.execute("""
    SELECT u.nombre, COUNT(t.id) 
    FROM usuarios u 
    LEFT JOIN tareas t ON u.id = t.encargado_id
    GROUP BY u.nombre
""")

resultados = cursor.fetchall()
usuarios = [row[0] for row in resultados]
total_tareas = [row[1] for row in resultados]

# Crear gráfico
plt.figure(figsize=(8, 5))
plt.bar(usuarios, total_tareas, color='skyblue')
plt.xlabel('Usuario')
plt.ylabel('Tareas Asignadas')
plt.title('Tareas Asignadas por Usuario')
plt.xticks(rotation=45)
plt.tight_layout()

# Guardar imagen PNG
plt.savefig('C:/xampp/htdocs/TEC/graficos/grafico_barras.png')
plt.close()

print("✅ Gráfico de barras guardado correctamente.")
