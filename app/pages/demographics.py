import streamlit as st
import pandas as pd
import plotly.express as px
from database import Database
from datetime import datetime

def app():
    st.title("人口统计信息管理")
    
    # 创建标签页
    tab1, tab2 = st.tabs(["录入信息", "数据统计"])
    
    # 录入信息标签页
    with tab1:
        with st.form("demographic_form"):
            name = st.text_input("姓名")
            birth_date = st.date_input("出生日期")
            gender = st.selectbox("性别", ["男", "女"])
            location = st.text_input("居住地")
            emergency_contact = st.text_input("紧急联系人")
            
            submit = st.form_submit_button("保存")
            
            if submit and name and birth_date:
                db = Database()
                query = """
                    INSERT INTO demographics 
                    (user_id, name, birth_date, gender, location, emergency_contact) 
                    VALUES (%s, %s, %s, %s, %s, %s)
                """
                if db.execute_query(query, (
                    st.session_state.user_id,
                    name,
                    birth_date,
                    gender,
                    location,
                    emergency_contact
                )):
                    st.success("信息保存成功！")
                else:
                    st.error("保存失败，请重试。")
                db.close()
    
    # 数据统计标签页
    with tab2:
        db = Database()
        query = "SELECT * FROM demographics WHERE user_id = %s"
        data = db.fetch_all(query, (st.session_state.user_id,))
        db.close()
        
        if data:
            df = pd.DataFrame(data)
            
            # 计算年龄
            df['age'] = df['birth_date'].apply(lambda x: 
                (datetime.now().date() - x).days // 365)
            
            # 基本统计信息
            st.subheader("基本统计")
            col1, col2 = st.columns(2)
            
            with col1:
                # 性别分布饼图
                fig_gender = px.pie(df, names='gender', title='性别分布')
                st.plotly_chart(fig_gender)
            
            with col2:
                # 年龄分布直方图
                fig_age = px.histogram(df, x='age', title='年龄分布')
                st.plotly_chart(fig_age)
            
            # 显示原始数据表格
            st.subheader("详细记录")
            st.dataframe(
                df[['name', 'birth_date', 'gender', 'location', 'emergency_contact']]
            )
        else:
            st.info("暂无数据记录")