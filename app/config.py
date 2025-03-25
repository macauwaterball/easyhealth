import os
from dotenv import load_dotenv

load_dotenv()

DB_CONFIG = {
    'host': os.getenv('MYSQL_HOST', 'db'),
    'user': os.getenv('MYSQL_USER', 'healthuser'),
    'password': os.getenv('MYSQL_PASSWORD', 'aa123456'),
    'database': os.getenv('MYSQL_DATABASE', 'health_db')
}

ADMIN_USERNAME = os.getenv('ADMIN_USERNAME', 'admin')
ADMIN_PASSWORD = os.getenv('ADMIN_PASSWORD', 'admin')  # 请在.env中修改
JWT_SECRET = os.getenv('JWT_SECRET', 'your_jwt_secret')  # 请在.env中修改