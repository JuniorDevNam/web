from django.urls import path
from . import views

urlpatterns = [
    path('truyenqq_leech_tool/', views.testing, name='testing'),
]