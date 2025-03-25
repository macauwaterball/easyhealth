import streamlit as st
import jwt
from datetime import datetime, timedelta
from config import ADMIN_USERNAME, ADMIN_PASSWORD, JWT_SECRET

def check_auth():
    if 'authenticated' not in st.session_state:
        st.session_state.authenticated = False
    
    if not st.session_state.authenticated:
        col1, col2 = st.columns([1, 2])
        with col1:
            username = st.text_input("用户名")
            password = st.text_input("密码", type="password")
            if st.button("登录"):
                if username == ADMIN_USERNAME and password == ADMIN_PASSWORD:
                    st.session_state.authenticated = True
                    token = generate_token()
                    st.session_state.token = token
                    st.experimental_rerun()
                else:
                    st.error("用户名或密码错误")
        st.stop()
    return True

def generate_token():
    payload = {
        'exp': datetime.utcnow() + timedelta(days=1),
        'iat': datetime.utcnow(),
        'sub': ADMIN_USERNAME
    }
    return jwt.encode(payload, JWT_SECRET, algorithm='HS256')

def verify_token(token):
    try:
        jwt.decode(token, JWT_SECRET, algorithms=['HS256'])
        return True
    except:
        return False