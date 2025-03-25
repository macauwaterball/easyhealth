import streamlit as st
import pandas as pd
import plotly.express as px
import plotly.graph_objects as go
from database import Database
from datetime import datetime

def app():
    st.title("不良习惯记录")
    
    tab1, tab2 = st.tabs(["记录", "趋势分析"])
    
    with tab1:
        with st.form("bad_habits_form"):
            record_date = st.date_input("记录日期", datetime.now())
            
            col1, col2 = st.columns(2)
            
            with col1:
                smoking_frequency = st.number_input("吸烟次数", 0, 100, 0)
                alcohol_frequency = st.number_input("饮酒次数", 0, 100, 0)
            
            with col2:
                quit_support_needed = st.checkbox("需要戒断支持")
                notes = st.text_area("备注", placeholder="记录其他相关情况...")
            
            submit = st.form_submit_button("保存记录")
            
            if submit:
                db = Database()
                query = """
                    INSERT INTO bad_habits 
                    (user_id, record_date, smoking_frequency, 
                    alcohol_frequency, quit_support_needed)
                    VALUES (%s, %s, %s, %s, %s)
                """
                if db.execute_query(query, (
                    st.session_state.user_id, record_date,
                    smoking_frequency, alcohol_frequency,
                    quit_support_needed
                )):
                    st.success("记录保存成功！")
                else:
                    st.error("保存失败，请重试。")
                db.close()
    
    with tab2:
        db = Database()
        query = """
            SELECT * FROM bad_habits 
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
            
            # 趋势分析
            st.subheader("习惯趋势")
            col1, col2 = st.columns(2)
            
            with col1:
                # 吸烟趋势
                fig_smoking = px.line(df, x='record_date', y='smoking_frequency',
                                   title='吸烟频率趋势')
                st.plotly_chart(fig_smoking)
                
                # 月度统计
                monthly_smoking = df.groupby(
                    df['record_date'].dt.strftime('%Y-%m')
                )['smoking_frequency'].mean()
                st.metric(
                    "本月平均吸烟次数",
                    f"{monthly_smoking.iloc[-1]:.1f}",
                    f"{monthly_smoking.iloc[-1] - monthly_smoking.iloc[-2]:.1f}"
                )
            
            with col2:
                # 饮酒趋势
                fig_alcohol = px.line(df, x='record_date', y='alcohol_frequency',
                                    title='饮酒频率趋势')
                st.plotly_chart(fig_alcohol)
                
                # 月度统计
                monthly_alcohol = df.groupby(
                    df['record_date'].dt.strftime('%Y-%m')
                )['alcohol_frequency'].mean()
                st.metric(
                    "本月平均饮酒次数",
                    f"{monthly_alcohol.iloc[-1]:.1f}",
                    f"{monthly_alcohol.iloc[-1] - monthly_alcohol.iloc[-2]:.1f}"
                )
            
            # 戒断支持需求分析
            st.subheader("戒断支持需求分析")
            support_needed = df['quit_support_needed'].value_counts()
            fig_support = px.pie(
                values=support_needed.values,
                names=support_needed.index,
                title="戒断支持需求比例"
            )
            st.plotly_chart(fig_support)
            
            # 详细记录
            st.subheader("详细记录")
            st.dataframe(df)
        else:
            st.info("暂无记录数据")