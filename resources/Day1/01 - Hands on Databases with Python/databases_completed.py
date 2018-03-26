# This scripts are possible solutions for the Database Tasks

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

    content = list(collection.find().sort([("age", pm.ASCENDING)]))

    return pd.DataFrame(content)


# Read the content of the SQL Lite File and return as Pandas Data Frame
def read_data_sqlite():
    print("Reading out Data with SQL Lite")
    conn_lite = sqll.connect("material/persons.db")

    query = "SELECT * FROM persons;"

    dataframe = pd.read_sql_query(query, conn_lite)

    return dataframe


def question_1_a(data_frame):
    print("Length of Persons List", data_frame['id'].count())


def question_1_b(data_frame):
    print("Length of Persons List", len(data_frame))


def question_1_c():
    conn = mysql.connect(
        host="185.239.237.221",
        port=3306,
        user="seminar_user",
        password="seminar_password",
        db="seminar"
    )

    query = "SELECT COUNT(*) FROM persons"

    dataframe = pd.read_sql(query, conn)
    print("Length of Persons List", dataframe['COUNT(*)'][0])


def question_2_a(data_frame):
    max_age = data_frame['age'].max()
    print("Oldest Person's age:", max_age)
    for index, item in data_frame.iterrows():
        if item["age"] == max_age:
            print(item)


def question_2_b(data_frame):
    conn = mysql.connect(
        host="185.239.237.221",
        port=3306,
        user="seminar_user",
        password="seminar_password",
        db="seminar"
    )
    max_age = data_frame['age'].max()
    query = "SELECT * FROM persons WHERE age=" + str(max_age)

    data_frame_max = pd.read_sql(query, conn)
    print(data_frame_max)


def question_2_c(data_frame):
    max_age = data_frame['age'].max()

    mongo_client = pm.MongoClient('mongodb://seminar_user:seminar_password@185.239.237.221:27017/seminar')
    mongo_db = mongo_client['seminar']
    collection = mongo_db['persons']

    content = list(collection.find({"age": 70}))
    data_frame_max = pd.DataFrame(content)
    print(data_frame_max)


def question_3_a(data_frame):
    summed_ages = 0
    number_ages = 0
    for index, item in data_frame.iterrows():
        summed_ages = summed_ages + item['age']
        number_ages = number_ages + 1
    print("Average age:", summed_ages / number_ages)


def question_3_b(data_frame):
    avg_age = data_frame['age'].mean()
    print("Average Age:", avg_age)


def question_5_a():
    conn = mysql.connect(
        host="185.239.237.221",
        port=3306,
        user="seminar_user",
        password="seminar_password",
        db="seminar"
    )

    query = "SELECT * FROM persons ORDER BY firstName  LIMIT 3"

    first_name_frame = pd.read_sql(query, conn)

    print(first_name_frame)


def question_5_b():
    mongo_client = pm.MongoClient('mongodb://seminar_user:seminar_password@185.239.237.221:27017/seminar')
    mongo_db = mongo_client['seminar']
    collection = mongo_db['persons']

    content = list(collection.find().limit(3).sort([("firstName", pm.ASCENDING)]))

    first_name_frame = pd.DataFrame(content)
    print(first_name_frame)


def question_5_c(data_frame):
    first_name_frame = data_frame.sort_values(by=['firstName']).head(3)
    print(first_name_frame)


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

    question_1_a(dataframe_mongodb)
    question_1_b(dataframe_mongodb)
    question_1_c()

    # Task 2: Who are the oldest persons?
    print("\n\n### TASK 2 ###\n\n")

    question_2_a(dataframe_mongodb)
    question_2_b(dataframe_mongodb)
    question_2_c(dataframe_mongodb)

    # Task 3: What is the average age of all persons?
    print("\n\n### TASK 3 ###\n\n")

    question_3_a(dataframe_mongodb)
    question_3_b(dataframe_mongodb)

    # Task 4: How many men and women are there?
    print("\n\n### TASK 4 ###\n\n")

    # Not to answer without additional knowledge!

    # Task 5: What are the alphabetically ordered first three first names
    print("\n\n### TASK 5 ###\n\n")

    question_5_a()
    question_5_b()
    question_5_c(dataframe_mongodb)
