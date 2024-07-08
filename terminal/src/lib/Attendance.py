import os
import requests

# LOAD ENVIRONMENT VARIABLES
import configparser

config = configparser.ConfigParser()
config.read(os.path.join(os.path.dirname(__file__), '../..', 'terminal.ini'))
SERVER_HOST = config['SERVER']['Host']
ATTENDANCE_API_ROUTE = SERVER_HOST + config['SERVER']['AttendanceApiRoute']


class Attendance:
    def entry(self, idnumber: str, testMode: False):
        try:
            params = {'idnumber': idnumber}

            if testMode:
                params['test'] = True

            r = requests.get(url=ATTENDANCE_API_ROUTE, params=params)
            if r.ok:
                json = r.json()
                if "student" in json:
                    self.student = json['student']
                if "transaction" in json:
                    self.transaction = json['transaction']
                if "error" in json:
                    self.error = json['error']

                return json
        except Exception as e:
            print(e)
            json = {'error': 'connectError'}
            self.error = "System is still initializing"
            return json
