import streamlit as st
import pandas as pd
import plotly.express as px
import plotly.graph_objects as go
from database import Database
from datetime import datetime

def app():
    st.title("认知情绪评估")
    
    tab1, tab2 = st.tabs(["评估记录", "趋势分析"])
    
    with tab1:
        with st.form("cognitive_form"):
            assessment_date = st.date_input("评估日期", datetime.now())
            
            st.subheader("简易精神状态检查(MMSE)")
            mmse_score = st.slider("MMSE得分", 0, 30, 28, 
                                 help="总分30分，≥27分为正常")
            
            st.subheader("老年抑郁量表(GDS)")
            gds_score = st.slider("GDS得分", 0, 15, 3,
                                help="总分15分，≤5分为正常")
            
            notes = st.text_area("评估备注", 
                placeholder="记录当前情绪状态、认知表现等...")
            
            submit = st.form_submit_button("保存评估")
            
            if submit:
                db = Database()
                query = """
                    INSERT INTO cognitive_assessment 
                    (user_id, assessment_date, mmse_score, gds_score, notes)
                    VALUES (%s, %s, %s, %s, %s)
                """
                if db.execute_query(query, (
                    st.session_state.user_id, assessment_date,
                    mmse_score, gds_score, notes
                )):
                    st.success("评估记录保存成功！")
                    
                    # 评估结果提示
                    if mmse_score < 27:
                        st.warning("MMSE得分偏低，建议进行专业认知功能评估")
                    if gds_score > 5:
                        st.warning("GDS得分偏高，建议进行情绪状态评估")
                else:
                    st.error("保存失败，请重试。")
                db.close()
    
    with tab2:
        db = Database()
        query = """
            SELECT * FROM cognitive_assessment 
            WHERE user_id = %s 
            ORDER BY assessment_date
        """
        data = db.fetch_all(query, (st.session_state.user_id,))
        db.close()
        
        if data:
            df = pd.DataFrame(data)
            
            # 趋势分析
            st.subheader("评分趋势")
            
            # MMSE趋势
            fig_mmse = go.Figure()
            fig_mmse.add_trace(go.Scatter(
                x=df['assessment_date'],
                y=df['mmse_score'],
                name="MMSE得分"
            ))
            fig_mmse.add_hline(
                y=27, 
                line_dash="dash", 
                annotation_text="正常参考线",
                line_color="green"
            )
            fig_mmse.update_layout(title="MMSE得分趋势")
            st.plotly_chart(fig_mmse)
            
            # GDS趋势
            fig_gds = go.Figure()
            fig_gds.add_trace(go.Scatter(
                x=df['assessment_date'],
                y=df['gds_score'],
                name="GDS得分"
            ))
            fig_gds.add_hline(
                y=5, 
                line_dash="dash", 
                annotation_text="正常参考线",
                line_color="green"
            )
            fig_gds.update_layout(title="GDS得分趋势")
            st.plotly_chart(fig_gds)
            
            # 统计摘要
            st.subheader("评估统计")
            col1, col2 = st.columns(2)
            
            with col1:
                st.metric(
                    "最新MMSE得分", 
                    df['mmse_score'].iloc[-1],
                    df['mmse_score'].iloc[-1] - df['mmse_score'].iloc[-2]
                )
                
            with col2:
                st.metric(
                    "最新GDS得分",
                    df['gds_score'].iloc[-1],
                    df['gds_score'].iloc[-1] - df['gds_score'].iloc[-2],
                    delta_color="inverse"
                )
            
            # 详细记录
            st.subheader("历史记录")
            st.dataframe(df)
        else:
            st.info("暂无评估记录")