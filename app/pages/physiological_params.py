import streamlit as st
import pandas as pd
import plotly.express as px
import plotly.graph_objects as go
from database import Database
from datetime import datetime, time

def app():
    st.title("生理参数记录")
    
    tab1, tab2 = st.tabs(["记录参数", "数据分析"])
    
    with tab1:
        with st.form("physiological_form"):
            col1, col2 = st.columns(2)
            
            with col1:
                measure_date = st.date_input("测量日期", datetime.now())
                measure_time = st.time_input("测量时间", time(8, 0))
                blood_pressure_sys = st.number_input("收缩压(mmHg)", 60, 250, 120)
                blood_pressure_dia = st.number_input("舒张压(mmHg)", 40, 150, 80)
                heart_rate = st.number_input("心率(次/分)", 40, 200, 75)
                heart_rhythm_normal = st.checkbox("心律正常", value=True)
            
            with col2:
                blood_sugar = st.number_input("血糖(mmol/L)", 0.0, 30.0, 5.6)
                hba1c = st.number_input("糖化血红蛋白(%)", 0.0, 20.0, 5.0)
                total_cholesterol = st.number_input("总胆固醇(mmol/L)", 0.0, 15.0, 4.5)
                ldl = st.number_input("低密度脂蛋白(mmol/L)", 0.0, 10.0, 2.5)
                hdl = st.number_input("高密度脂蛋白(mmol/L)", 0.0, 5.0, 1.5)
                triglycerides = st.number_input("甘油三酯(mmol/L)", 0.0, 10.0, 1.7)
            
            submit = st.form_submit_button("保存记录")
            
            if submit:
                db = Database()
                query = """
                    INSERT INTO physiological_params 
                    (user_id, measure_date, measure_time, blood_pressure_sys, 
                    blood_pressure_dia, blood_sugar, hba1c, total_cholesterol, 
                    ldl, hdl, triglycerides, heart_rate, heart_rhythm_normal)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                """
                if db.execute_query(query, (
                    st.session_state.user_id, measure_date, measure_time,
                    blood_pressure_sys, blood_pressure_dia, blood_sugar,
                    hba1c, total_cholesterol, ldl, hdl, triglycerides,
                    heart_rate, heart_rhythm_normal
                )):
                    st.success("记录保存成功！")
                else:
                    st.error("保存失败，请重试。")
                db.close()
    
    with tab2:
        db = Database()
        query = """
            SELECT * FROM physiological_params 
            WHERE user_id = %s 
            ORDER BY measure_date, measure_time
        """
        data = db.fetch_all(query, (st.session_state.user_id,))
        db.close()
        
        if data:
            df = pd.DataFrame(data)
            
            # 参数选择
            param_groups = {
                "血压": ["blood_pressure_sys", "blood_pressure_dia"],
                "血糖": ["blood_sugar", "hba1c"],
                "血脂": ["total_cholesterol", "ldl", "hdl", "triglycerides"],
                "心率": ["heart_rate"]
            }
            
            selected_group = st.selectbox("选择参数组", list(param_groups.keys()))
            
            # 时间范围选择
            date_range = st.date_input(
                "选择日期范围",
                [df['measure_date'].min(), df['measure_date'].max()]
            )
            
            # 绘制趋势图
            fig = go.Figure()
            
            if selected_group == "血压":
                fig.add_trace(go.Scatter(
                    x=df['measure_date'],
                    y=df['blood_pressure_sys'],
                    name="收缩压"
                ))
                fig.add_trace(go.Scatter(
                    x=df['measure_date'],
                    y=df['blood_pressure_dia'],
                    name="舒张压"
                ))
            elif selected_group == "血糖":
                fig.add_trace(go.Scatter(
                    x=df['measure_date'],
                    y=df['blood_sugar'],
                    name="血糖"
                ))
                fig.add_trace(go.Scatter(
                    x=df['measure_date'],
                    y=df['hba1c'],
                    name="糖化血红蛋白"
                ))
            elif selected_group == "血脂":
                for param in param_groups["血脂"]:
                    fig.add_trace(go.Scatter(
                        x=df['measure_date'],
                        y=df[param],
                        name=param
                    ))
            else:
                fig.add_trace(go.Scatter(
                    x=df['measure_date'],
                    y=df['heart_rate'],
                    name="心率"
                ))
            
            fig.update_layout(
                title=f"{selected_group}趋势图",
                xaxis_title="日期",
                yaxis_title="数值"
            )
            st.plotly_chart(fig)
            
            # 统计摘要
            st.subheader("统计摘要")
            col1, col2, col3 = st.columns(3)
            
            with col1:
                st.metric("最新血压", 
                    f"{df['blood_pressure_sys'].iloc[-1]}/{df['blood_pressure_dia'].iloc[-1]}")
            with col2:
                st.metric("最新血糖", f"{df['blood_sugar'].iloc[-1]:.1f}")
            with col3:
                st.metric("最新心率", f"{df['heart_rate'].iloc[-1]}")
            
            # 详细数据表格
            st.subheader("详细记录")
            st.dataframe(df)
        else:
            st.info("暂无记录数据")