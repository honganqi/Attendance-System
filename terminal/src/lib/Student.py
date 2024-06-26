import os
from datetime import datetime
import requests
from lib.fromDB import Db

# LOAD ENVIRONMENT VARIABLES
import configparser

config = configparser.ConfigParser()
config.read(os.path.join(os.path.dirname(__file__), '..', 'terminal.ini'))
SERVER_HOST = config['SERVER']['Host']
STUDENT_API_ROUTE = SERVER_HOST + 'api/student/'


class Student:
    def __init__(self, studentId: str):
        if studentId:
            self.id = studentId

    def getRecord(self) -> object:
        try:
            conn = Db()
            query = """SELECT id, lastname, firstname, middlename, suffix, nickname, birthdate, gender FROM students WHERE id = %s"""
            params = (self.id, )
            results = conn.executeQuery(query, params)
            generatedName = self.makeName(results)
            results["name"] = generatedName
            self.attendanceEntry(results["ID"], conn)
            return results

        except Exception as e:
            print(f"Error: {e}")

        finally:
            conn.closeConnection()
    
    def getStudentIDFromIDNumber(self, idnumber) -> str:
        try:
            params = {'idnumber': idnumber}
            print('idnumber: ', idnumber)
            print("requests.get")
            r = requests.get(url=STUDENT_API_ROUTE + '/get', params=params)
        except requests.exceptions.ConnectionError as e:
            print(f"Error: {e}")
            return False

    def makeName(self, nameArray) -> str:
        lastname = nameArray["lastname"]
        firstname = nameArray["firstname"]
        middlename = nameArray["middlename"]
        suffix = nameArray["suffix"]

        if middlename != "":
            middlename = f"{middlename[0]}."

        if suffix != "":
            suffix = f"{suffix} "

        return f"{lastname}, {firstname} {suffix}{middlename}"