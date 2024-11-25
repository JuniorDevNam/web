from django.urls import path
from . import views

urlpatterns = [
    path('truyenqq_leech_tool/', views.Tool, name='tool'),
]