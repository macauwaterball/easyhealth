import streamlit as st
from database import Database

def app():
    patient = st.session_state.current_patient
    if not patient:
        st.warning("请先选择患者")
        return
        
    st.subheader(f"生理参数记录 - {patient['name']} ({patient['birth_date']})")
    
    with st.form("physiological_form"):
        date = st.date_input("记录日期")
        blood_pressure = st.text_input("血压 (mmHg)")
        heart_rate = st.number_input("心率 (次/分)", min_value=0)
        temperature = st.number_input("体温 (℃)", min_value=35.0, max_value=42.0, value=36.5)
        
        submit = st.form_submit_button("保存")
        if submit:
            db = Database()
            try:
                # 添加记录
                query = """
                    INSERT INTO physiological_params 
                    (patient_id, date, blood_pressure, heart_rate, temperature)
                    VALUES (%s, %s, %s, %s, %s)
                """
                db.execute_query(query, (
                    patient['id'], date, blood_pressure, heart_rate, temperature
                ))
                st.success("记录保存成功！")
            except Exception as e:
                st.error(f"保存失败：{str(e)}")
            finally:
                db.close()