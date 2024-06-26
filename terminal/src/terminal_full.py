import os
import tkinter as tk
from tkinter import *
from tkinter import font as tkfont
import threading
import time
import json
import nfc
from nfc import ContactlessFrontend
from nfc.clf import RemoteTarget
import requests
from io import BytesIO
from PIL import ImageTk, Image
from lib.Attendance import Attendance
import random

from datetime import datetime
import cv2



# LOAD ENVIRONMENT VARIABLES
import configparser

config = configparser.ConfigParser()
config.read(os.path.join(os.path.dirname(__file__), '..', 'terminal.ini'))
TIMEOUT_BEFORE_MINIMIZE = config['SETTINGS']['TimeoutBeforeMinimize']	# minimizing this window shows the clock that's supposed to run behind
SERVER_HOST = config['SERVER']['Host']
ATTENDANCE_API_ROUTE = config['SERVER']['AttendanceApiRoute']
STUDENT_ID_PLACEHOLDER = config['SERVER']['StudentIdPlaceholder']
FETCHER_ID_PLACEHOLDER = config['SERVER']['FetcherIdPlaceholder']
TEST_MODE = config['SETTINGS']['TestMode']

# VARIABLES
taptime = 0
elapsed_time = 0
previous_user = None
previous_type = None





# the Clock includes a subtle background that changes 
class Clock(tk.Frame):
	# Clock Variables
	screenwidth = 1
	screenheight = 1
	COLOR_SHIFT_INTERVAL = 500
	c1 = 0
	c1_forward = True
	c2 = 0
	c2_forward = True
	red1 = 0
	red2 = 0
	green1 = 0
	green2 = 0
	blue1 = 0
	blue2 = 0
	MAX_COLOR = 2
	MIN_COLOR = 0
	red_dir = 1
	green_dir = 0
	blue_dir = 0
	reset_dir = 0


	def __init__(self, parent):
		tk.Canvas.__init__(self, parent)
		screenwidth = self.winfo_screenwidth()
		screenheight = self.winfo_screenheight()
		dateFontSize = int(screenwidth * 0.025)
		timeFontSize = int(screenwidth * 0.085)

		self.canvas = tk.Canvas(self)
		self.canvas.pack(side="top", fill="both", expand=True)

		labelXPos = screenwidth / 2
		dateYPos = (screenheight / 2) - int(screenheight * 0.105)
		timeYPos = (screenheight / 2) + int(screenheight * 0.025)

		self.labelDate = self.canvas.create_text((labelXPos, dateYPos), text="Date here", font=('arial', dateFontSize, 'bold'), fill="#fff")
		self.labelTime = self.canvas.create_text((labelXPos, timeYPos), text="Time here", font=('arial', timeFontSize, 'bold'), fill="#fff")
		self.startClock()

	def tick(self):
		self.canvas.itemconfig(self.labelDate, text=time.strftime('%A, %B %e, %G'))
		self.canvas.itemconfig(self.labelTime, text=time.strftime('%I:%M:%S %p'))

		hexstring = "#{}{}{}{}{}{}".format(self.red1,self.red2,self.green1,self.green2,self.blue1,self.blue2)

		if self.red2 <= Clock.MIN_COLOR and self.green2 <= Clock.MIN_COLOR and (self.blue_dir == 0 or self.blue2 >= self.MAX_COLOR):
			self.red_dir = 1
		elif self.red2 >= Clock.MAX_COLOR and self.green2 >= Clock.MAX_COLOR:
			self.red_dir = -1
			self.green_dir = 0
		elif self.red2 >= Clock.MAX_COLOR and self.green2 <= Clock.MIN_COLOR:
			self.red_dir = 0
			self.green_dir = 1
		elif self.red2 <= Clock.MIN_COLOR:
			self.red_dir = 0

		if self.green2 <= Clock.MIN_COLOR and self.blue2 <= Clock.MIN_COLOR and self.red2 >= Clock.MAX_COLOR:
			self.green_dir = 1
		elif self.green2 >= Clock.MAX_COLOR and self.blue2 >= Clock.MAX_COLOR:
			self.green_dir = -1
			self.blue_dir = 0
		elif self.green2 >= Clock.MAX_COLOR and self.blue2 <= Clock.MIN_COLOR:
			self.green_dir = 0
			self.blue_dir = 1
		elif self.green2 <= Clock.MIN_COLOR:
			self.green_dir = 0

		if self.blue2 <= Clock.MIN_COLOR and self.red2 <= Clock.MIN_COLOR and self.green2 >= Clock.MAX_COLOR:
			self.blue_dir = 1
		elif self.blue2 >= Clock.MAX_COLOR and self.red2 >= Clock.MAX_COLOR:
			self.blue_dir = -1
			self.red_dir = -1
		elif self.blue2 >= Clock.MAX_COLOR and self.red2 <= Clock.MIN_COLOR and self.green2 <= Clock.MIN_COLOR:
			if self.red_dir == 1:
				self.blue_dir = 0
				self.red_dir = 1
			else:
				self.blue_dir = -1
				self.red_dir = 0
		elif self.blue2 <= Clock.MIN_COLOR:
			self.blue_dir = 0

		self.red1, self.red2 = self.update_color(self.red1, self.red2, self.red_dir)
		self.green1, self.green2 = self.update_color(self.green1, self.green2, self.green_dir)
		self.blue1, self.blue2 = self.update_color(self.blue1, self.blue2, self.blue_dir)

		self.canvas.config(bg=hexstring)
		self.config(bg=hexstring)
		self.after(Clock.COLOR_SHIFT_INTERVAL, self.tick)

	def startClock(self):
		self.tick()

	def update_color(self, color1, color2, color_dir):
		if color_dir == 1:
			if color1 > color2:
				color2 += color_dir
			else:
				color1 += color_dir
		elif color_dir == -1:
			if color1 >= color2:
				color1 += color_dir
			else:
				color2 += color_dir
		return color1, color2



class NumberDisplay(tk.Toplevel):
	def __init__(self):
		super().__init__()
		
		self.text = Text(self, font=('arial', 24, 'bold'), bg="white", wrap="word", borderwidth=0, relief="flat", height=1, highlightthickness=0)
		#self.text.configure(state="disabled")
		self.text.pack(expand=True, fill='both')

		# Bind the left mouse button click event to the label
		self.text.bind("<Button-1>", self.copyToClipboard)
	def showNumber(self, number):
		self.iconify()
		self.deiconify()
		self.text.delete("1.0", "end")
		self.text.insert("1.0", number)
	def copyToClipboard(self, event):
		# Get the text from the label
		text = self.text.get("1.0", "end")
		# Clear the clipboard
		self.clipboard_clear()
		# Append the text to the clipboard
		self.clipboard_append(text)
		# Notify the user (optional)
		print(f"Copied to clipboard: {text}")


# Start the nfcpy reader
# Make sure you have the ACR122U USB NFC reader
try:
	clf = ContactlessFrontend('usb')
except IOError:
	clf = None










class Terminal(tk.Tk):

	def __init__(self, *args, **kwargs):
		tk.Tk.__init__(self, *args, **kwargs)
		
		self.title_font = tkfont.Font(family='Helvetica', size=18, weight="bold", slant="italic")
		self.attributes('-fullscreen', True)
		self.title("Terminal")
		# "container" is where we'll stack frames on top of each other,
		# then the one we want visible will be raised above the others
		container = tk.Frame(self)
		container.pack(side="top", fill="both", expand=True)
		container.update()
		framewidth = container.winfo_width()
		frameheight = container.winfo_height()

		# Create the Clock instance
		self.clock = tk.Tk()
		self.clock.title("Clock")
		self.clock.attributes('-fullscreen', True)
		Clock(self.clock).grid(sticky="nsew")
		self.clock.grid_rowconfigure(0, weight=1)
		self.clock.grid_columnconfigure(0, weight=1)

		# Create the NumberDisplay instance
		self.numberDisplay = NumberDisplay()
		self.numberDisplay.title("ID Display")

		self.directory = os.path.dirname(os.path.realpath(__file__))

		frame = UserFrame(parent=container, controller=self, framewidth=framewidth, frameheight=frameheight)
		self.frame = frame

		# if TEST_MODE is set to True, attach keypress (spacebar) to load fake students into frame
		if TEST_MODE == 'True':
			self.clock.bind("<space>", self.keyPress)


		# put all of the pages in the same location;
		# the one on the top of the stacking order
		# will be the one that is visible.
		frame.grid(row=0, column=0, sticky="nsew")

		self.iconify()
		
		if clf is None:
			self.deiconify()
			frame.showError("RFID reader not detected\n\nConnect the USB NFC Reader \nand restart the computer", sysError=True)

	def keyPress(self, e):
		# get a random idnumber from the array below
		# these are also set as static data in the backend
		idnumbers = ['11111111', '22222222', '33333333', '44444444', '55555555', '66666666', '77777777', '88888888']
		idnumber = random.choice(idnumbers)

		idResult = self.frame.loadID(idnumber, testMode=True)
		if (idResult is not None):
			self.deiconify()
			self.frame.showNumber(idnumber)
		else:
			self.numberDisplay.iconify()
			self.deiconify()

class UserFrame(tk.Frame):
	def __init__(self, parent, controller, framewidth, frameheight):
		tk.Frame.__init__(self, parent)
		self.controller = controller
		thisFrame = Frame(self)
		self.framewidth = framewidth
		self.frameheight = frameheight

		# NAME font is 3% of frame size
		# ACTION font is 2.0% of frame size
		# LOG font is 1.0% of frame size
		userName_FontSize = int(framewidth * 0.02)
		self.userClass_FontSize = int(framewidth * 0.012)
		self.userAction_FontSize = int(framewidth * 0.03)
		self.userLog_FontSize = int(framewidth * 0.011)
		userLog_IconSize = int(self.userLog_FontSize*1.6)
		self.gatepass_FontSize = int(framewidth * 0.011)
		
		if clf is None:
			self.userAction_FontSize = int(framewidth * 0.02)
		
		# primary frames
		self.photoFrame = Frame(thisFrame, width=int(framewidth*0.4), padx=int(framewidth*0.03), bg="white")
		infoFrameOuter = Frame(thisFrame, width=int(framewidth*0.6), height=int(frameheight), bg="white")
		infoFrame = Frame(infoFrameOuter)
		infoFrame.pack(side="top", anchor="nw", fill="both", expand=True)

		self.signin = self.imgResize(os.path.join(controller.directory, "signin.png"), self.userAction_FontSize)
		self.signout = self.imgResize(os.path.join(controller.directory, "signout.png"), self.userAction_FontSize)
		self.login = self.imgResize(os.path.join(controller.directory, "signin.png"), userLog_IconSize)
		self.logout = self.imgResize(os.path.join(controller.directory, "signout.png"), userLog_IconSize)

		# name
		self.nameFrame = Frame(infoFrame, bg="white")
		self.userName = Text(self.nameFrame, font=('arial', userName_FontSize, 'bold'), bg="white", wrap="word", borderwidth=0, relief="flat", height=1, highlightthickness=0)
		self.userName.pack(side="top", pady=(100,20), fill="both", expand=True)
		self.className = Text(self.nameFrame, font=('arial', self.userClass_FontSize), bg="white", wrap="word", borderwidth=0, relief="flat", height=1, highlightthickness=0)
		self.className.pack(side="top", fill="both", expand=True)
		self.nameFrame.pack(side="top", fill="both", expand=False)

		self.userPhoto = Label(self.photoFrame, text="", width=int(framewidth*0.3), bg="white")
		self.userPhoto.pack(side="top", fill="both", expand=True)
		self.photoMaxWidth = int(framewidth*0.3)

		canvas = Canvas(infoFrame, width=int(framewidth*0.5), height=2, bd=0, highlightthickness=0)
		canvas.pack(side="top",expand=False)
		canvas.create_line(0, 0,int(canvas.winfo_width()*0.7),0)

		self.actionFrame = Frame(infoFrame, bg="white")
		self.userAction = Label(self.actionFrame, font=('arial', self.userAction_FontSize, 'bold'), anchor="nw", justify=LEFT, bg="white")
		self.userAction.pack(side="top", fill="both", expand=True)
		self.actionFrame.pack(side="top", fill="both", expand=False)

		self.logFrame = Frame(infoFrame, bg="white", height=int(frameheight*0.1))
		self.logFrame.pack(side="top", fill="both", expand=True)
		self.logFrame.pack_propagate(False)
		self.logSection = Frame(self.logFrame, bg="white")
		self.gatepassSection = Frame(self.logFrame, bg="white")
		self.gatepassLabel = Label(self.gatepassSection, text="", font=('arial', self.gatepass_FontSize), anchor="nw", justify=LEFT, bg="white")
		self.gatepassLabel.pack(side="top", fill="y", expand=True, anchor="nw")

		self.fetchersFrame = Frame(infoFrame, bg="white", height=int(frameheight*0.05))
		self.fetchersFrame.pack(side="top", fill="both", expand=False)
		self.fetchersFrameInterior1 = Frame(self.fetchersFrame, bg="white")
		self.fetchersFrameInterior1.pack(side="top", fill="x", expand=False)
		self.fetchersFrameInterior2 = Frame(self.fetchersFrame, bg="white")
		self.fetchersFrameInterior2.pack(side="top", fill="x", expand=False)

		self.photoFrame.grid(row=0, column=0, sticky="nsew")
		self.photoFrame.pack_propagate(False)
		infoFrameOuter.grid(row=0, column=1, sticky="nsew")
		infoFrameOuter.pack_propagate(False)
		thisFrame.pack(side=TOP, fill=BOTH)
		thisFrame.pack_propagate(False)
		thisFrame.rowconfigure(0, weight=1)
		thisFrame.columnconfigure(0, weight=35)
		thisFrame.columnconfigure(1, weight=65)

	def loadID(self, idnumber):
		global previous_user
		global previous_type
		post_fields = {'idnumber': idnumber}

		# reset variables
		json_data = {}

		try:
			record = Attendance()
			record.entry(idnumber, testMode)
			if record != False:
				try:
					# if "error" in record:
					# 	raise Exception(record.error)
					self.wipeFrame()

					if hasattr(record, 'error'):
						raise Exception(record.error)
					
					if hasattr(record, 'notfound'):
						#raise Exception("ID number not assigned to any student\n" + idnumber)
						raise Exception("ID number not assigned to any student")

					student = record.student
					transaction = record.transaction

					if "class" not in json_data or ("class" in json_data and "class" == ""):
						#self.className.destroy()
						className = ""
					else:
						#self.className.insert("1.0", json_data["class"])
						className = json_data["class"]

					self.printLog("", "Checking entry/exit")
					# json response should return a "double" if tapped multiple times in quick succession (left on scanner)
					# if "double" not in json_data: something needs to be done with current action, NOT get current time but get last recorded time
					if "double" in json_data:
						userActionText = json_data["double"]["time"]
						json_data["inoutInsert"] = json_data["double"]["in_out"]
					else:
						userActionText = time.strftime('%r')

					userActionIcon = self.signin
					userActionImagePos = "left"
					if json_data["inoutInsert"] == 1:
						userActionIcon = self.signout
						userActionImagePos = "right"
						userActionText = userActionText + " "
					else:
						userActionText = " " + userActionText

					self.printLog("", "Processing photo")
					if "photo" not in json_data or ("photo" in json_data and json_data["photo"] == ""):
						self.printLog("Photo", "not found")
						photo = MERIDIAN_ID_PLACEHOLDER
					else:
						photo_year_list = json_data["photo"].keys()
						photo_most_recent_index = list(photo_year_list)[0]
						photo = json_data["photo"][photo_most_recent_index]
						self.printLog("Photo", "is in variable")
					try:
						photopath = os.path.join(os.path.dirname(__file__), "..", photo)
						if os.path.isfile(photopath) != False:
							self.printLog("Photo", "is found in file")
							image = photopath
						else:
							self.printLog("Photo", "not found in filesystem")
							image = False
					except Exception as e:
						self.printLog("Photo", "exception", e)
						image = False
						
					if image != False:
						try:
							self.printLog("", "Resizing photo")
							userPhoto = self.imgResize(image, int(self.frameheight*0.7), isUserPhoto=True)
						except OSError as e:
							print("Image error: " + str(e))

					# logs
					logs = []
					if "log" in json_data:
						logs = json_data["log"]
					
					# gatepass
					if "gatepass" in json_data and json_data["gatepass"] != "":
						gatepassString = "Gate Pass: \n   " + json_data["gatepass"]
					else:
						gatepassString = ""


					self.printLog("", "Reinitializing frame")
					self.reinitializeFrame(
						userName=json_data["name"],
						className=className,
						userActionText=userActionText,
						userActionImage=userActionIcon,
						userActionImagePos=userActionImagePos,
						userPhoto=userPhoto,
						logs=logs,
						gatepass=gatepassString
						)

					fetcher_ctr = 1
					if "fetchers" in json_data:
						for fetcher_id, photo in json_data["fetchers"].items():
							if photo == "":
								photo = FETCHER_ID_PLACEHOLDER
							try:
								photopath = os.path.join(os.path.dirname(__file__), "..", photo)
								if os.path.isfile(photopath) != False:
									image = photopath
								else:
									image = False
							except Exception as e:
								image = False
							if image != False:
								try:
									img = self.imgResize(image, int(self.framewidth*0.1))
									if fetcher_ctr > 5:
										fetcherPhoto = Label(self.fetchersFrameInterior2, text="", width=int(self.framewidth*0.12), bg="white")
									else:
										fetcherPhoto = Label(self.fetchersFrameInterior1, text="", width=int(self.framewidth*0.12), bg="white")
									fetcherPhoto.pack(side="left")
									fetcherPhoto.configure(image=img)
									fetcherPhoto.image=img
									fetcher_ctr = fetcher_ctr + 1
								except OSError as e:
									print("Image error: " + str(e))
					self.printLog("", "Setting previous user")
					previous_user = json_data["id"]
					previous_type = json_data["type"]

				except ValueError as e:
					print('ValueError')
					self.showError("ID not found in system")
				

			else:
				if hasattr(record, 'error'):
					errorMessage = record.error
				else:
					errorMessage = "Cannot connect to server"
				self.showError(errorMessage)
				   

		except Exception as e:
			print(e)
			if hasattr(record, 'error'):
				errorMessage = record.error
			else:
				errorMessage = str(e)
			self.showError(errorMessage)
			if hasattr(record, 'notfound'):
				return "NOTFOUND"

	def wipeFrame(self):
		try:
			self.userName.delete("1.0", "end")
			self.className.delete("1.0", "end")
			self.userAction.pack_forget()
			self.logSection.pack_forget()
			# destroy all widgets from frame
			for log in self.logSection.winfo_children():
				log.destroy()
			self.gatepassSection.pack_forget()
			self.userPhoto.pack_forget()
			self.fetchersFrameInterior1.pack_forget()
			self.fetchersFrameInterior2.pack_forget()
		except Exception as e:
			print(e)

	def reinitializeFrame(self, userName, className, userActionText, userActionImage, userActionImagePos, userPhoto, logs, gatepass):
		self.printLog("reinitializeFrame", "userName className")
		#  text containers
		self.userName.insert("1.0", userName)
		if className != None:
			self.className.insert("1.0", className)

		self.printLog("reinitializeFrame", "userAction")
		# current action section
		self.userAction.configure(text=userActionText, image=userActionImage, compound=userActionImagePos)
		self.userAction.pack(side="top", fill="both", expand=True)

		# photo section
		self.userPhoto.configure(image=userPhoto)
		self.userPhoto.image=userPhoto
		self.userPhoto.pack(side="top", fill="both", expand=True)

		# log section
		if logs:
			for logdata in logs:
				if logdata["action"] == 1:
					action_icon = self.logout
				else:
					action_icon = self.login
				logstring = " " + logdata["time"]
				userLog = Label(self.logSection, text=logstring, font=('arial', self.userLog_FontSize), anchor="nw", justify=LEFT, bg="white")
				userLog.pack(side="top", fill="y", expand=False, anchor="nw")
				userLog.configure(image=action_icon, compound="left")
				userLog.image=action_icon
		self.logSection.pack(side="left", fill="y", expand=True, anchor="nw")

		# gate pass section
		if gatepass:
			self.gatepassLabel.configure(text=gatepass)
			self.gatepassSection.pack(side="top", fill="y", expand=True, anchor="nw")
		else:
			self.gatepassLabel.configure(text="")
			self.gatepassSection.pack_forget()

		#reset fetchers section
		self.fetchersFrameInterior1.pack_forget()
		#self.fetchersFrameInterior1.destroy()
		self.fetchersFrameInterior2.pack_forget()
		#self.fetchersFrameInterior2.destroy()
		self.fetchersFrameInterior1 = Frame(self.fetchersFrame, bg="white")
		self.fetchersFrameInterior1.pack(side="top", fill="x", expand=False)
		self.fetchersFrameInterior2 = Frame(self.fetchersFrame, bg="white")
		self.fetchersFrameInterior2.pack(side="top", fill="x", expand=False)
		'''

	def showError(self, errorMessage, sysError=False):
		self.wipeFrame()
		self.reinitializeFrame(
			nickname="Error",
			fullname=errorMessage,
			age=""
		)
		if sysError == True:
			self.controller.iconify()
			self.controller.deiconify()


	def imgResize(self, filename, maxheight):
		try:
			'''
			print(datetime.now(), " -- imgResize: Image.open()")
			img = Image.open(filename)
			print(datetime.now(), " -- imgResize: hpercent")
			hpercent = (maxheight / float(img.size[1]))
			print(datetime.now(), " -- imgResize: wsize")
			wsize = int((float(img.size[0]) * float(hpercent)))
			print(datetime.now(), " -- imgResize: img.resize")
			tempimage = img.resize((wsize, maxheight))
			print(datetime.now(), " -- imgResize: ImageTk.PhotoImage")
			img = ImageTk.PhotoImage(tempimage, master=self)
			'''

			# Read the image
			self.printLog("imgResize", "cv2.imread()")
			img = cv2.imread(filename)

			# Rearrange the color channel
			b,g,r = cv2.split(img)
			img = cv2.merge((r,g,b))

			# Get and set the dimensions
			hpercent = (maxheight / float(img.shape[0]))
			wsize = int((float(img.shape[1]) * float(hpercent)))
			tempimage = cv2.resize(img, (wsize, maxheight))

			if isUserPhoto:
				imgwidth = tempimage.shape[1]
				difference = imgwidth - self.photoMaxWidth
				if difference > 0:
					cropAmount = difference // 2
					xEnd = imgwidth - cropAmount
					# Crop from {x, y, w, h } => {0, 0, 300, 400}
					self.printLog("imgResize", "crop")
					tempimage = tempimage[0:maxheight, cropAmount:xEnd]

			try:
				# Convert the Image object into a TkPhoto object
				im = Image.fromarray(tempimage)
				
				self.printLog("imgResize", "ImageTk.PhotoImage")
				img = ImageTk.PhotoImage(image=im, master=self)
			except Exception as e:
				print("Some error")
				print(e)

			return img
		except IOError as e:
			print("Image error: " + str(e))

	def showNumber(self, idnumber):
		self.controller.numberDisplay.showNumber(idnumber)
 
 






















terminal = Terminal()
terminal.title("Attendance Terminal")




# Function to close the RFID window after some inactivity (modify the TIMEOUT_BEFORE_MINIMIZE variable)
def close_rfid_window():
	terminal.withdraw()
	terminal.numberDisplay.withdraw()

# Function to handle RFID tag detection
def connected(tag):
	number = []
	for c in tag.identifier:
		number.append(c)

	clf.device.turn_on_led_and_buzzer() # LED should go red with a brief buzzer sound

	
	# reverse the bytes found in tag since that's how our RFID cards were found and programmed in 2013
	number.reverse()
	idnumber = ''.join(str(c) for c in number)
	
	# show ID
	# root.info('Loading ID...')
	idResult = terminal.frame.loadID(idnumber)
	if (idResult is not None):
		terminal.deiconify()
		terminal.frame.showNumber(idnumber)
	else:
		terminal.numberDisplay.iconify()
		terminal.deiconify()
	
	# Reset the timer when a new tag is detected
	global timer_thread
	if timer_thread and timer_thread.is_alive():
		timer_thread.cancel()

	# Start the timer to close the RFID window after 30 seconds of inactivity
	timer_thread = threading.Timer(TIMEOUT_BEFORE_MINIMIZE, close_rfid_window)
	timer_thread.start()


def waitForTag():
	loop = RemoteTarget("106A")
	try:
		while True:
			target = clf.sense(loop, iterations=1, interval=0.8)
			if target:
				tag = nfc.tag.activate(clf, target)
				connected(tag)
			time.sleep(0.5)
	finally:
		print("Restarting NFC Reader...")
		waitForTag()

# Global variable to store the timer thread
timer_thread = None

if __name__ == "__main__":
	print("Attendance Terminal Interface started")
	if clf is not None:
		t1 = threading.Thread(target=waitForTag)
		t1.daemon = True
		t1.start()
	else:
		terminal.frame.showError("RFID reader not detected\n\nConnect the USB NFC Reader \nand restart the computer", sysError=True)


	terminal.mainloop()
