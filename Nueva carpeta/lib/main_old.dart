from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, declarative_base
from dotenv import load_dotenv
import os

load_dotenv()

SERVER = os.getenv('DB_SERVER', 'localhost')
DATABASE = os.getenv('DB_NAME', 'biblioteca')
USERNAME = os.getenv('DB_USER', 'root')
PASSWORD = os.getenv('DB_PASS', '')

connection_string = (
    f"mssql+pyodbc://{USERNAME}:{PASSWORD}@{SERVER}/{DATABASE}"
    "?driver=ODBC+Driver+17+for+SQL+Server"
)

engine = create_engine(connection_string)
SessionLocal = sessionmaker(bind=engine, autoflush=False)
Base = declarative_base()