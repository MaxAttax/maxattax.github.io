# Task 3: Scalar Product

# Calculates the scalar product of two vectors
def scalar(x, y):
	
	# Compares the length of both vectors, because a scalar product is only possible if the vectors have an equal length
    if len(x) != len(y):
	
		# if the length of the two vectors is not equal, the function should return "None", because there is no result
        return None
	
	# In the case that the length of the two vectors is equal
    else:
	
		# Initialize a variable to store the scalar product result
        scalar_product = 0
		
		# Loop through the indices of the vectors (from 0 to index=len(vector)-1)
		# Explanation: The last value in the range(first, last) function will not be looped, so the (last - 1) value is actually (len(vector) - 1)
        for index in range(0, len(x)):
            
			# Update the scalar product for each index of the vectors and add it to the last value
			scalar_product = scalar_product + x[index] * y[index]
    
	# Return the calculated scalar product
	return scalar_product
	

# Inject example vectors and print the results
if __name__ == '__main__':
    vector_x = [1, 2, 3]
    vector_y = [4, 5, 6]
    print("\nResults for Scalar Product of", vector_x, "and", vector_y)
    print(scalar(vector_x, vector_y))