import bcrypt
from database import Database

class UserManager:
    def __init__(self):
        self.db = Database()

    def create_user(self, username, password):
        hashed = bcrypt.hashpw(password.encode('utf-8'), bcrypt.gensalt())
        query = "INSERT INTO users (username, password_hash) VALUES (%s, %s)"
        return self.db.execute_query(query, (username, hashed))

    def verify_user(self, username, password):
        query = "SELECT id, password_hash FROM users WHERE username = %s"
        user = self.db.fetch_one(query, (username,))
        if user and bcrypt.checkpw(password.encode('utf-8'), user['password_hash'].encode('utf-8')):
            return user['id']
        return None

    def get_user_info(self, user_id):
        query = """
        SELECT u.username, d.* 
        FROM users u 
        LEFT JOIN demographics d ON u.id = d.user_id 
        WHERE u.id = %s
        """
        return self.db.fetch_one(query, (user_id,))