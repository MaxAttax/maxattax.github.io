# Task 2: Maximum Value calculation


def maximum(value_list):

	# Initialize the maximum value with the first value in the list
    max_value = value_list[0]
	
	# Iterate through all values of the list
    for value in value_list:
	
		# If the current value in the list is higher than the actual max value ...
        if value > max_value:
		
			# ... then assign this new value to the max_value and redifine the new maximum
            max_value = value
			
	# Return the found maximum value
    return max_value


def maximum2(value_list):

	# Simple solution by making use of the build-in Python function "max()"
    return max(value_list)
	

# Running the function with example data
if __name__ == '__main__':
    maximum_list = [5, 2, 89, 34, 545, 23, 57, 234, 464]
    print("\nResults for Maximum in List", maximum_list)
    print(maximum(maximum_list))
