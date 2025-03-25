import mysql.connector
import os

class Database:
    def __init__(self):
        self.connection = mysql.connector.connect(
            host=os.getenv('MYSQL_HOST', 'db'),
            database=os.getenv('MYSQL_DATABASE', 'health_db'),
            user=os.getenv('MYSQL_USER', 'healthuser'),
            password=os.getenv('MYSQL_PASSWORD', 'aa123456')
        )
        self.cursor = self.connection.cursor(dictionary=True)

    def fetch_one(self, query, params=None):
        self.cursor.execute(query, params or ())
        return self.cursor.fetchone()

    def fetch_all(self, query, params=None):
        self.cursor.execute(query, params or ())
        return self.cursor.fetchall()

    def execute_query(self, query, params=None):
        self.cursor.execute(query, params or ())
        self.connection.commit()
        return True

    def close(self):
        self.cursor.close()
        self.connection.close()