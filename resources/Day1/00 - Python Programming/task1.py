# Task 1: Calculation of the Faculty through Python

def faculty_for(value):
	
	# Initialize the number that will store the faculty
    faculty = 1
	
	# Create a for loop that will loop through all numbers from 1 to the faculty number
    for index in range(1, value+1):
        # Multiply the variable by the looping variable to update the number in each loop
		faculty = faculty * index
    
	# Return the end value of the calculated faculty
	return faculty


def faculty_while(value):

	# Initialize the number that will store the faculty
    faculty = 1
    
	# Create a while loop that counts down the value from faculty to one.
	while value > 1:
		# Multiply the variable by the looping variable to update the number in each loop
        faculty = faculty * value
		# Reduce the multiplication number until it is 1
        value = value-1
    
	# Return the end value of the calculated faculty
	return faculty
	

def faculty_recursive(value):
    # Solution with a recursive loop that calls itself until the value is smaller than 1
	if value < 2:
        return 1
    else:
		# Multiply the faculty number with the new number
        return value * faculty_recursive(value-1)
	

# Run all programs to check if they work
if __name__ == '__main__':
    n = 6
    print("\nResults for Faculty of", n, ":")
    print("Faculty, calculated with for-loop:", faculty_for(n))
    print("Faculty, calculated with while-loop:", faculty_while(n))
    print("Faculty, calculated recursively:", faculty_recursive(n))
