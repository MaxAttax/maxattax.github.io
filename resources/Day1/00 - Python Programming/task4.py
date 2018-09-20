# Task 4: Accept comma-separated input from user and put result into a list and into a tuple

# values=raw_input()	# Python 2.7
values=input()		# Python 3
l=values.split(",")
t=tuple(l)
print(l)
print(t)