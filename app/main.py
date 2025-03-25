import streamlit as st
from database import Database
from datetime import datetime

# 页面配置
st.set_page_config(
    page_title="健康管理系统",
    page_icon="🏥",
    layout="wide"
)

# 初始化session state
if 'user_id' not in st.session_state:
    st.session_state.user_id = None
if 'current_user' not in st.session_state:
    st.session_state.current_user = None
if 'current_patient' not in st.session_state:
    st.session_state.current_patient = None

def calculate_age(birth_date):
    today = datetime.now()
    age = today.year - birth_date.year
    if today.month < birth_date.month or (today.month == birth_date.month and today.day < birth_date.day):
        age -= 1
    return age

def search_patient():
    st.subheader("患者搜索")
    col1, col2 = st.columns([2, 1])
    
    with col1:
        search_type = st.radio("搜索方式", ["姓名", "电话"], horizontal=True)
        search_value = st.text_input("请输入搜索内容")
        search = st.button("搜索")
        
    if search and search_value:
        db = Database()
        try:
            if search_type == "姓名":
                query = "SELECT * FROM demographics WHERE name = %s"
            else:
                query = "SELECT * FROM demographics WHERE phone = %s"
                
            patient = db.fetch_one(query, (search_value,))
            
            if patient:
                st.session_state.current_patient = patient
                st.success(f"找到患者：{patient['name']}")
            else:
                st.warning("未找到患者记录")
                with st.form("new_patient"):
                    st.subheader("创建新患者记录")
                    name = st.text_input("姓名")
                    phone = st.text_input("电话")
                    birth_date = st.date_input("出生日期")
                    gender = st.selectbox("性别", ["男", "女"])
                    
                    submit = st.form_submit_button("创建")
                    if submit and name and phone:
                        try:
                            insert_query = """
                                INSERT INTO demographics (name, phone, birth_date, gender)
                                VALUES (%s, %s, %s, %s)
                            """
                            db.execute_query(insert_query, (name, phone, birth_date, gender))
                            
                            # 获取新创建的患者记录
                            new_patient = db.fetch_one(
                                "SELECT * FROM demographics WHERE name = %s AND phone = %s",
                                (name, phone)
                            )
                            if new_patient:
                                st.session_state.current_patient = new_patient
                                st.success(f"患者 {name} 创建成功！")
                        except Exception as e:
                            st.error(f"创建失败：{str(e)}")
        finally:
            db.close()

def search_user():
    st.subheader("社区用户搜索")
    col1, col2 = st.columns([2, 1])
    
    with col1:
        search_type = st.radio("搜索方式", ["姓名", "电话"], horizontal=True)
        search_value = st.text_input("请输入搜索内容")
        search = st.button("搜索")
        
    if search and search_value:
        db = Database()
        try:
            if search_type == "姓名":
                query = "SELECT * FROM demographics WHERE name = %s"
            else:
                query = "SELECT * FROM demographics WHERE phone = %s"
                
            user = db.fetch_one(query, (search_value,))
            
            if user:
                st.session_state.current_user = user
                st.success(f"找到社区用户：{user['name']}")
            else:
                st.warning("未找到用户记录")
                with st.form("new_user"):
                    st.subheader("创建新用户记录")
                    name = st.text_input("姓名")
                    phone = st.text_input("电话")
                    birth_date = st.date_input("出生日期")
                    gender = st.selectbox("性别", ["男", "女"])
                    
                    submit = st.form_submit_button("创建")
                    if submit and name and phone:
                        try:
                            insert_query = """
                                INSERT INTO demographics (name, phone, birth_date, gender)
                                VALUES (%s, %s, %s, %s)
                            """
                            db.execute_query(insert_query, (name, phone, birth_date, gender))
                            
                            new_user = db.fetch_one(
                                "SELECT * FROM demographics WHERE name = %s AND phone = %s",
                                (name, phone)
                            )
                            if new_user:
                                st.session_state.current_user = new_user
                                st.success(f"社区用户 {name} 创建成功！")
                        except Exception as e:
                            st.error(f"创建失败：{str(e)}")
        finally:
            db.close()

# Add after the calculate_age function and before search_patient function
def login():
    st.title("社区健康管理系统")
    with st.form("login_form"):
        username = st.text_input("用户名")
        password = st.text_input("密码", type="password")
        submit = st.form_submit_button("登录")
        
        if submit:
            db = Database()
            try:
                user = db.fetch_one(
                    "SELECT * FROM users WHERE username = %s AND password = %s",
                    (username, password)
                )
                
                if user:
                    st.session_state.user_id = user['id']
                    st.success("登录成功！")
                    st.experimental_rerun()
                else:
                    st.error("用户名或密码错误！")
            finally:
                db.close()

def main():
    if not st.session_state.user_id:
        login()
    else:
        st.sidebar.title("社区健康管理系统")
        
        search_user()
        
        if st.session_state.current_user:
            user = st.session_state.current_user
            st.info(f"""
                当前用户：{user['name']} | 
                年龄：{calculate_age(user['birth_date'])}岁 | 
                性别：{user['gender']}
            """)
            
            menu = st.sidebar.selectbox(
                "功能菜单",
                ["生理参数", "体格指标", "生活习惯", "饮食记录", 
                 "不良习惯", "认知评估", "基本信息"]
            )
            
            # 根据菜单选择加载对应页面
            if menu == "生理参数":
                from pages.physiological_params import app
                app()
            elif menu == "体格指标":
                from pages.physical_metrics import app
                app()
            elif menu == "生活习惯":
                from pages.lifestyle_habits import app
                app()
            elif menu == "饮食记录":
                from pages.diet_records import app
                app()
            elif menu == "不良习惯":
                from pages.bad_habits import app
                app()
            elif menu == "认知评估":
                from pages.cognitive_assessment import app
                app()
            elif menu == "基本信息":
                from pages.demographics import app
                app()
            
            # 显示患者搜索
            search_patient()
            
            # 如果选择了患者，显示患者信息和功能菜单
            if st.session_state.current_patient:
                patient = st.session_state.current_patient
                st.info(f"""
                    当前患者：{patient['name']} | 
                    年龄：{calculate_age(patient['birth_date'])}岁 | 
                    性别：{patient['gender']}
                """)
                
                # 显示功能菜单
                menu = st.sidebar.selectbox(
                    "功能菜单",
                    ["生理参数", "体格指标", "生活习惯", "饮食记录", 
                     "不良习惯", "认知评估", "人口统计"]
                )
                
                # 根据菜单选择加载对应页面
                if menu == "生理参数":
                    from pages.physiological_params import app
                    app()
                elif menu == "体格指标":
                    from pages.physical_metrics import app
                    app()
                elif menu == "生活习惯":
                    from pages.lifestyle_habits import app
                    app()
                elif menu == "饮食记录":
                    from pages.diet_records import app
                    app()
                elif menu == "不良习惯":
                    from pages.bad_habits import app
                    app()
                elif menu == "认知评估":
                    from pages.cognitive_assessment import app
                    app()
                elif menu == "人口统计":
                    from pages.demographics import app
                    app()
        
        # 添加退出按钮
        if st.sidebar.button("退出登录"):
            st.session_state.user_id = None
            st.session_state.current_patient = None
            st.experimental_rerun()

if __name__ == "__main__":
    main()
