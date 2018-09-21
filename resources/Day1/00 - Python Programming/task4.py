# Task 4: Accept comma-separated input from user and put result into a list and into a tuple

# The program waits until the user inputs a String into the console
# For this task, the user should write some comma-separated integer values into the console and press "Enter" 
values = input()			# Use for Python 3

# After the user pressed "Enter", the rest of the program from here is executed
# The split()-operator splits a given String at the indicated separator (here a comma ",")
# The split(",") function is used with the value that carries the user input from above (values)
l = values.split(",")

# A tuple is a special kind of list that can not be modified or extended. 
# The tuple() function generates a tuple from a given list.
t = tuple(l)

# Finally the results are printed out.
print(l)
print(t)