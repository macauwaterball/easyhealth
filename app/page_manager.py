import streamlit as st
from pages import (
    demographics,
    physical_metrics,
    physiological_params,
    lifestyle_habits,
    diet_records,
    bad_habits,
    cognitive_assessment
)

class PageManager:
    def __init__(self):
        self.pages = {
            "人口统计": demographics.app,
            "身体指标": physical_metrics.app,
            "生理参数": physiological_params.app,
            "生活习惯": lifestyle_habits.app,
            "饮食记录": diet_records.app,
            "不良习惯": bad_habits.app,
            "认知评估": cognitive_assessment.app
        }
    
    def render_page(self, page_name):
        if page_name in self.pages:
            self.pages[page_name]()