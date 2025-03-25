import streamlit as st
import pandas as pd
import plotly.express as px
import plotly.graph_objects as go
from database import Database
from datetime import datetime

def app():
    st.title("生活习惯记录")
    
    tab1, tab2 = st.tabs(["日常记录", "数据分析"])
    
    with tab1:
        with st.form("lifestyle_form"):
            record_date = st.date_input("记录日期", datetime.now())
            
            col1, col2 = st.columns(2)
            
            with col1:
                steps_count = st.number_input("步数", 0, 100000, 5000)
                exercise_type = st.selectbox(
                    "运动类型",
                    ["步行", "跑步", "游泳", "太极", "瑜伽", "其他"]
                )
                exercise_duration = st.number_input("运动时长(分钟)", 0, 480, 30)
                sitting_duration = st.number_input("久坐时长(小时)", 0, 24, 8)
            
            with col2:
                sleep_duration = st.number_input("睡眠时长(小时)", 0.0, 24.0, 7.0)
                deep_sleep_duration = st.number_input("深睡时长(小时)", 0.0, 12.0, 3.0)
                wake_count = st.number_input("夜间醒来次数", 0, 10, 0)
            
            submit = st.form_submit_button("保存记录")
            
            if submit:
                db = Database()
                query = """
                    INSERT INTO lifestyle_habits 
                    (user_id, record_date, steps_count, exercise_type, 
                    exercise_duration, sitting_duration, sleep_duration, 
                    deep_sleep_duration, wake_count)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
                """
                if db.execute_query(query, (
                    st.session_state.user_id, record_date, steps_count,
                    exercise_type, exercise_duration, sitting_duration,
                    sleep_duration, deep_sleep_duration, wake_count
                )):
                    st.success("记录保存成功！")
                else:
                    st.error("保存失败，请重试。")
                db.close()
    
    with tab2:
        db = Database()
        query = """
            SELECT * FROM lifestyle_habits 
            WHERE user_id = %s 
            ORDER BY record_date
        """
        data = db.fetch_all(query, (st.session_state.user_id,))
        db.close()
        
        if data:
            df = pd.DataFrame(data)
            
            # 活动分析
            st.subheader("活动分析")
            col1, col2 = st.columns(2)
            
            with col1:
                # 步数趋势
                fig_steps = px.line(df, x='record_date', y='steps_count',
                                  title='每日步数趋势')
                st.plotly_chart(fig_steps)
                
                # 运动类型分布
                fig_exercise = px.pie(df, names='exercise_type',
                                    title='运动类型分布')
                st.plotly_chart(fig_exercise)
            
            with col2:
                # 睡眠质量分析
                fig_sleep = go.Figure()
                fig_sleep.add_trace(go.Bar(
                    name='总睡眠',
                    x=df['record_date'],
                    y=df['sleep_duration']
                ))
                fig_sleep.add_trace(go.Bar(
                    name='深睡',
                    x=df['record_date'],
                    y=df['deep_sleep_duration']
                ))
                fig_sleep.update_layout(
                    title='睡眠质量分析',
                    barmode='overlay'
                )
                st.plotly_chart(fig_sleep)
            
            # 统计摘要
            st.subheader("统计摘要")
            col1, col2, col3 = st.columns(3)
            
            with col1:
                avg_steps = df['steps_count'].mean()
                st.metric("平均步数", 
                         f"{avg_steps:.0f}",
                         f"{df['steps_count'].iloc[-1] - avg_steps:.0f}")
            
            with col2:
                avg_sleep = df['sleep_duration'].mean()
                st.metric("平均睡眠时长",
                         f"{avg_sleep:.1f}小时",
                         f"{df['sleep_duration'].iloc[-1] - avg_sleep:.1f}")
            
            with col3:
                avg_exercise = df['exercise_duration'].mean()
                st.metric("平均运动时长",
                         f"{avg_exercise:.0f}分钟",
                         f"{df['exercise_duration'].iloc[-1] - avg_exercise:.0f}")
            
            # 详细记录
            st.subheader("详细记录")
            st.dataframe(df)
        else:
            st.info("暂无记录数据")