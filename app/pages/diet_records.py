import streamlit as st
import pandas as pd
import plotly.express as px
import plotly.graph_objects as go
from database import Database
from datetime import datetime

def app():
    st.title("饮食记录")
    
    tab1, tab2 = st.tabs(["记录饮食", "数据分析"])
    
    with tab1:
        with st.form("diet_form"):
            record_date = st.date_input("记录日期", datetime.now())
            
            col1, col2 = st.columns(2)
            
            with col1:
                calories = st.number_input("热量摄入(kcal)", 0.0, 5000.0, 2000.0)
                salt_intake = st.number_input("盐分摄入(g)", 0.0, 20.0, 6.0)
                sugar_intake = st.number_input("糖分摄入(g)", 0.0, 200.0, 50.0)
            
            with col2:
                water_intake = st.number_input("水分摄入(ml)", 0.0, 5000.0, 2000.0)
                special_diet = st.text_area("特殊饮食需求", 
                    placeholder="例如：低盐、低糖、流质饮食等")
            
            submit = st.form_submit_button("保存记录")
            
            if submit:
                db = Database()
                query = """
                    INSERT INTO diet_records 
                    (user_id, record_date, calories, salt_intake, 
                    sugar_intake, water_intake, special_diet)
                    VALUES (%s, %s, %s, %s, %s, %s, %s)
                """
                if db.execute_query(query, (
                    st.session_state.user_id, record_date, calories,
                    salt_intake, sugar_intake, water_intake, special_diet
                )):
                    st.success("记录保存成功！")
                else:
                    st.error("保存失败，请重试。")
                db.close()
    
    with tab2:
        db = Database()
        query = """
            SELECT * FROM diet_records 
            WHERE user_id = %s 
            ORDER BY record_date
        """
        data = db.fetch_all(query, (st.session_state.user_id,))
        db.close()
        
        if data:
            df = pd.DataFrame(data)
            
            # 时间范围选择
            date_range = st.date_input(
                "选择日期范围",
                [df['record_date'].min(), df['record_date'].max()]
            )
            
            # 营养摄入趋势
            st.subheader("营养摄入趋势")
            
            # 选择要显示的指标
            metrics = st.multiselect(
                "选择要显示的指标",
                ["热量", "盐分", "糖分", "水分"],
                default=["热量"]
            )
            
            fig = go.Figure()
            
            if "热量" in metrics:
                fig.add_trace(go.Scatter(
                    x=df['record_date'],
                    y=df['calories'],
                    name="热量(kcal)"
                ))
            if "盐分" in metrics:
                fig.add_trace(go.Scatter(
                    x=df['record_date'],
                    y=df['salt_intake'],
                    name="盐分(g)"
                ))
            if "糖分" in metrics:
                fig.add_trace(go.Scatter(
                    x=df['record_date'],
                    y=df['sugar_intake'],
                    name="糖分(g)"
                ))
            if "水分" in metrics:
                fig.add_trace(go.Scatter(
                    x=df['record_date'],
                    y=df['water_intake'],
                    name="水分(ml)"
                ))
            
            fig.update_layout(
                title="营养摄入趋势图",
                xaxis_title="日期",
                yaxis_title="数值"
            )
            st.plotly_chart(fig)
            
            # 统计摘要
            st.subheader("统计摘要")
            col1, col2, col3, col4 = st.columns(4)
            
            with col1:
                avg_calories = df['calories'].mean()
                st.metric("平均热量", 
                         f"{avg_calories:.0f}kcal",
                         f"{df['calories'].iloc[-1] - avg_calories:.0f}")
            
            with col2:
                avg_salt = df['salt_intake'].mean()
                st.metric("平均盐分",
                         f"{avg_salt:.1f}g",
                         f"{df['salt_intake'].iloc[-1] - avg_salt:.1f}")
            
            with col3:
                avg_sugar = df['sugar_intake'].mean()
                st.metric("平均糖分",
                         f"{avg_sugar:.1f}g",
                         f"{df['sugar_intake'].iloc[-1] - avg_sugar:.1f}")
            
            with col4:
                avg_water = df['water_intake'].mean()
                st.metric("平均水分",
                         f"{avg_water:.0f}ml",
                         f"{df['water_intake'].iloc[-1] - avg_water:.0f}")
            
            # 详细记录
            st.subheader("详细记录")
            st.dataframe(df)
        else:
            st.info("暂无记录数据")