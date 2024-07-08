import os
import tkinter as tk
from tkinter import *
from tkinter import font as tkfont
import threading
import time
import nfc
from nfc import ContactlessFrontend
from nfc.clf import RemoteTarget
from PIL import ImageTk, Image
from lib.Attendance import Attendance
import random




# LOAD ENVIRONMENT VARIABLES
import configparser

config = configparser.ConfigParser()
config.read(os.path.join(os.path.dirname(__file__), '..', 'terminal.ini'))
SERVER_HOST = config['SERVER']['Host']
ATTENDANCE_API_ROUTE = config['SERVER']['AttendanceApiRoute']
TEST_MODE = config['SETTINGS']['TestMode']

try:
	TIMEOUT_BEFORE_MINIMIZE = int(config['SETTINGS']['TimeoutBeforeMinimize'])	# minimizing this window shows the clock that's supposed to run behind
except ValueError:
	TIMEOUT_BEFORE_MINIMIZE = 30

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
	def __init__(self, container):
		super().__init__()
		
		screen_width = container.winfo_screenwidth()
		window_width = 350
		window_height = 60
		xpos = screen_width - window_width
		ypos = 0
		self.geometry(f"{window_width}x{window_height}+{xpos}+{ypos}")
		self.text = Text(self, font=('arial', 24, 'bold'), bg="white", wrap="word", borderwidth=0, relief="flat", height=1, highlightthickness=0)
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
		self.numberDisplay = NumberDisplay(self)
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

		# NICKNAME font is 5% of frame size
		# FULLNAME font is 2% of frame size
		nickname_FontSize = int(framewidth * 0.05)
		self.fullname_FontSize = int(framewidth * 0.02)
		
		# primary frames
		infoFrameOuter = Frame(thisFrame, width=int(framewidth), height=int(frameheight), bg="red")

		# name
		nicknameframe = Frame(infoFrameOuter, bg="white")
		nicknameframe.pack(side="top", anchor="nw", fill="both", expand=True)
		fullnameFrame = Frame(infoFrameOuter, bg="white")
		fullnameFrame.pack(side="bottom", anchor="sw", fill="both", expand=True)
		self.nickname = Text(nicknameframe, font=('arial', nickname_FontSize, 'bold'), bg="white", wrap="word", borderwidth=0, relief="flat", height=1, highlightthickness=0)
		self.nickname.pack(side="bottom", fill="both", expand=False)
		self.nickname.tag_configure("centerText", justify="center")
		self.fullname = Text(fullnameFrame, font=('arial', self.fullname_FontSize), bg="white", wrap="word", borderwidth=0, relief="flat", height=1, highlightthickness=0)
		self.fullname.pack(fill="both", expand=True)
		self.fullname.tag_configure("centerText", justify="center")

		infoFrameOuter.grid(row=0, column=1, sticky="nsew")
		infoFrameOuter.pack_propagate(False)
		thisFrame.pack(side=TOP, fill=BOTH)
		thisFrame.pack_propagate(False)
		thisFrame.rowconfigure(0, weight=1)
		thisFrame.columnconfigure(0, weight=35)
		thisFrame.columnconfigure(1, weight=65)

	def loadID(self, idnumber, testMode=False):
		global previous_user
		global previous_type

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
					
					student = record.student
					transaction = record.transaction

					self.reinitializeFrame(
						nickname=student['nickname'],
						fullname=student['fullname'],
						age=""
					)

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
			if hasattr(record, 'error'):
				return "NOTFOUND"

	def wipeFrame(self):
		try:
			self.nickname.delete("1.0", "end")
			self.fullname.delete("1.0", "end")
		except Exception as e:
			print(e)

	def reinitializeFrame(self, nickname, fullname, age):
		self.nickname.insert("1.0", nickname, "center")
		self.fullname.insert("1.0", fullname + "\n" + age, "center")
		self.nickname.tag_add("centerText", "1.0", "end")
		self.fullname.tag_add("centerText", "1.0", "end")

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
			img = Image.open(filename)
			hpercent = (maxheight / float(img.size[1]))
			wsize = int((float(img.size[0]) * float(hpercent)))
			img = ImageTk.PhotoImage(img.resize((wsize, maxheight)), master=self)
			return img
		except IOError as e:
			print("Image error: " + str(e))

	def showNumber(self, idnumber):
		self.controller.numberDisplay.showNumber(idnumber)
 
 






















terminal = Terminal()
terminal.title("Attendance Terminal")




# Minimize the information window after a period of inactivity (modify the TIMEOUT_BEFORE_MINIMIZE variable)
def minimizeInfoWindow():
	terminal.withdraw()
	terminal.numberDisplay.withdraw()

# Function to handle RFID tag detection
def connected(tag):
	number = []
	for c in tag.identifier:
		number.append(c)

	clf.device.turn_on_led_and_buzzer() # LED should go red with a brief buzzer sound

	
	# reverse the bytes found in tag
	number.reverse()
	idnumber = ''.join(str(c) for c in number)
	
	# show ID
	idResult = terminal.frame.loadID(idnumber, testMode=False)
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

	# Start the timer to minimize the information window after a period of inactivity
	timer_thread = threading.Timer(TIMEOUT_BEFORE_MINIMIZE, minimizeInfoWindow)
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
