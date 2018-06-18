# Packages that might have to be installed via pip/conda:
# conda install pandas
# conda install MySQL-python
# conda install mysqlclient
# conda install pymongo
# conda install sqlite3

# General Packages
import pandas as pd
# Packages for MySQL
import MySQLdb as mysql
# Packages for MongoDB
import pymongo as pm
# Packages for SQLite
import sqlite3 as sqll

# Basic Methods for reading the content of MySQL, MongoDB and SQL Lite

# Read the content of the MySQL Table and return as Pandas Data Frame
def read_data_mysql():
    print("Reading out Data with MYSQL")
    conn = mysql.connect(
        host ="185.239.237.221",
        port=3306,
        user="seminar_user",
        password="seminar_password",
        db="seminar"
    )

    query = "SELECT * FROM persons"

    dataframe = pd.read_sql(query, conn)

    return dataframe


# Read the content of the MongoDB Collection and return as Pandas Data Frame
def read_data_mongodb():
    print("Reading out Data with MongoDB")
    mongo_client = pm.MongoClient('mongodb://seminar_user:seminar_password@185.239.237.221:27017/seminar')
    mongo_db = mongo_client['seminar']
    collection = mongo_db['persons']

    content = list(collection.find())

    return pd.DataFrame(content)


# Read the content of the SQL Lite File and return as Pandas Data Frame
def read_data_sqlite():
    print("Reading out Data with SQL Lite")
    conn_lite = sqll.connect("material/persons.db")

    query = "SELECT * FROM persons;"

    dataframe = pd.read_sql_query(query, conn_lite)

    return dataframe


if __name__ == '__main__':
    print("MongoDB Result")
    dataframe_mongodb = read_data_mongodb()
    print(dataframe_mongodb)

    print("MySQL Result")
    dataframe_mysql = read_data_mysql()
    print(dataframe_mysql)

    print("SQLite Results")
    dataframe_sqlite = read_data_sqlite()
    print(dataframe_sqlite)

    # Task 1: How many entries are there?
    print("\n\n### TASK 1 ###\n\n")

    #TODO: Code for Task 1


    # Task 2: Who are the oldest persons?
    print("\n\n### TASK 2 ###\n\n")

    # TODO: Code for Task 2

    # Task 3: What is the average age of all persons?
    print("\n\n### TASK 3 ###\n\n")

    # TODO: Code for Task 3

    # Task 4: How many men and women are there?
    print("\n\n### TASK 4 ###\n\n")

    # TODO: Code for Task 4

    # Task 5: What are the alphabetically ordered first three first names
    print("\n\n### TASK 5 ###\n\n")

    # TODO: Code for Task 5

