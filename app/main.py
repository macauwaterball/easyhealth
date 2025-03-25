import streamlit as st
from auth import check_auth
import plotly.express as px
from datetime import datetime

st.set_page_config(
    page_title="健康管理系统",
    page_icon="🏥",
    layout="wide"
)

# 验证登录
if check_auth():
    st.title("健康管理系统")
    
    # 侧边栏导航
    st.sidebar.title("导航菜单")
    page = st.sidebar.selectbox(
        "选择功能",
        ["主页", "人口统计", "身体指标", "生理参数", "生活习惯", "饮食记录", "不良习惯", "认知评估"]
    )
    
    if page == "主页":
        st.write("欢迎使用健康管理系统")
        
        # 显示最近的健康指标
        col1, col2, col3 = st.columns(3)
        
        with col1:
            st.metric(label="最新BMI", value="23.5", delta="1.2")
        
        with col2:
            st.metric(label="最新血压", value="120/80", delta="-5")
            
        with col3:
            st.metric(label="今日步数", value="8000", delta="500")
            
        # 添加快速记录按钮
        if st.button("快速记录今日数据"):
            st.session_state.page = "身体指标"
            st.experimental_rerun()