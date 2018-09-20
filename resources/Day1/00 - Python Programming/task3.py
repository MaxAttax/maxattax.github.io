# Task 3: Scalar Product

def scalar(x, y):
    if len(x) != len(y):
        return None
    else:
        scalar_product = 0
        for index in range(0, len(x)):
            scalar_product = scalar_product + x[index] * y[index]
    return scalar_product
	
	
if __name__ == '__main__':
    vector_x = [1, 2, 3]
    vector_y = [4, 5, 6]
    print("\nResults for Scalar Product of", vector_x, "and", vector_y)
    print(scalar(vector_x, vector_y))