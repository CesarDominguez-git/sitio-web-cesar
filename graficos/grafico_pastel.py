import os
import mysql.connector
import matplotlib.pyplot as plt

# 1️⃣ Conexión y datos
conn = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',
    database='TEC'
)
cursor = conn.cursor()
cursor.execute("SELECT estado, COUNT(*) FROM tareas GROUP BY estado")
result = cursor.fetchall()

labels, sizes = [], []
for estado, count in result:
    labels.append(estado)
    sizes.append(count)

plt.pie(sizes, labels=labels, autopct='%1.1f%%', startangle=140)
plt.title('Distribución de Tareas por Estado')
plt.axis('equal')

# 2️⃣ 🔥 Guarda SIEMPRE en la carpeta de tu proyecto XAMPP:
# Por ejemplo: C:\xampp\htdocs\mi_proyecto\graficos\
ruta_base = r'C:\xampp\htdocs\TEC\graficos'

if not os.path.exists(ruta_base):
    os.makedirs(ruta_base)

ruta_img = os.path.join(ruta_base, 'grafico_pastel.png')
plt.savefig(ruta_img)

print(f"Guardado en: {ruta_img}")

cursor.close()
conn.close()
