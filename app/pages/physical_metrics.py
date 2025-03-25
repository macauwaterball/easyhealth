import streamlit as st
import pandas as pd
import plotly.express as px
import plotly.graph_objects as go
from database import Database
from datetime import datetime

def calculate_bmi(weight, height):
    if height <= 0 or weight <= 0:
        return 0
    return weight / ((height/100) ** 2)

def app():
    st.title("身体指标记录")
    
    tab1, tab2 = st.tabs(["记录指标", "数据分析"])
    
    with tab1:
        with st.form("physical_metrics_form"):
            col1, col2 = st.columns(2)
            
            with col1:
                measure_date = st.date_input("测量日期", datetime.now())
                height = st.number_input("身高(cm)", 0.0, 300.0, 170.0)
                weight = st.number_input("体重(kg)", 0.0, 300.0, 60.0)
                
            with col2:
                waist = st.number_input("腰围(cm)", 0.0, 200.0, 80.0)
                bone_density = st.number_input("骨密度(g/cm²)", 0.0, 5.0, 1.0)
                
            bmi = calculate_bmi(weight, height)
            st.write(f"计算得出BMI: {bmi:.2f}")
            
            submit = st.form_submit_button("保存记录")
            
            if submit:
                db = Database()
                query = """
                    INSERT INTO physical_metrics 
                    (user_id, measure_date, height, weight, bmi, waist, bone_density)
                    VALUES (%s, %s, %s, %s, %s, %s, %s)
                """
                if db.execute_query(query, (
                    st.session_state.user_id,
                    measure_date,
                    height,
                    weight,
                    bmi,
                    waist,
                    bone_density
                )):
                    st.success("记录保存成功！")
                else:
                    st.error("保存失败，请重试。")
                db.close()
    
    with tab2:
        db = Database()
        query = """
            SELECT measure_date, height, weight, bmi, waist, bone_density 
            FROM physical_metrics 
            WHERE user_id = %s 
            ORDER BY measure_date
        """
        data = db.fetch_all(query, (st.session_state.user_id,))
        db.close()
        
        if data:
            df = pd.DataFrame(data)
            
            # 趋势图
            st.subheader("指标趋势")
            metrics = st.multiselect(
                "选择要显示的指标",
                ["BMI", "体重", "腰围", "骨密度"],
                default=["BMI"]
            )
            
            fig = go.Figure()
            if "BMI" in metrics:
                fig.add_trace(go.Scatter(
                    x=df['measure_date'],
                    y=df['bmi'],
                    name="BMI"
                ))
            if "体重" in metrics:
                fig.add_trace(go.Scatter(
                    x=df['measure_date'],
                    y=df['weight'],
                    name="体重(kg)"
                ))
            if "腰围" in metrics:
                fig.add_trace(go.Scatter(
                    x=df['measure_date'],
                    y=df['waist'],
                    name="腰围(cm)"
                ))
            if "骨密度" in metrics:
                fig.add_trace(go.Scatter(
                    x=df['measure_date'],
                    y=df['bone_density'],
                    name="骨密度(g/cm²)"
                ))
            
            fig.update_layout(
                title="身体指标变化趋势",
                xaxis_title="日期",
                yaxis_title="数值"
            )
            st.plotly_chart(fig)
            
            # 统计表格
            st.subheader("详细记录")
            st.dataframe(df)
            
            # 基本统计信息
            st.subheader("统计摘要")
            col1, col2, col3 = st.columns(3)
            with col1:
                st.metric("最新BMI", f"{df['bmi'].iloc[-1]:.2f}", 
                         f"{df['bmi'].iloc[-1] - df['bmi'].iloc[-2]:.2f}")
            with col2:
                st.metric("平均体重", f"{df['weight'].mean():.1f}kg",
                         f"{df['weight'].iloc[-1] - df['weight'].mean():.1f}")
            with col3:
                st.metric("最新腰围", f"{df['waist'].iloc[-1]}cm",
                         f"{df['waist'].iloc[-1] - df['waist'].iloc[-2]}")
        else:
            st.info("暂无记录数据")