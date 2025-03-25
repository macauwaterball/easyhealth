import mysql.connector
from mysql.connector import Error
from config import DB_CONFIG
import pandas as pd

class Database:
    def __init__(self):
        try:
            self.connection = mysql.connector.connect(**DB_CONFIG)
            self.cursor = self.connection.cursor(dictionary=True)
        except Error as e:
            print(f"数据库连接错误: {e}")
            raise

    def execute_query(self, query, params=None):
        try:
            self.cursor.execute(query, params or ())
            self.connection.commit()
            return True
        except Error as e:
            print(f"查询执行错误: {e}")
            return False

    def fetch_all(self, query, params=None):
        try:
            self.cursor.execute(query, params or ())
            return self.cursor.fetchall()
        except Error as e:
            print(f"数据获取错误: {e}")
            return []

    def fetch_one(self, query, params=None):
        try:
            self.cursor.execute(query, params or ())
            return self.cursor.fetchone()
        except Error as e:
            print(f"数据获取错误: {e}")
            return None

    def close(self):
        try:
            self.cursor.close()
            self.connection.close()
        except Error as e:
            print(f"关闭连接错误: {e}")