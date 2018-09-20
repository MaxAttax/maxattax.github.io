# Task 2: Maximum Value calculation

def maximum(value_list):
    max_value = value_list[0]
    for value in value_list:
        if value > max_value:
            max_value = value
    return max_value


def maximum2(value_list):
    return max(value_list)
	

if __name__ == '__main__':
    maximum_list = [5, 2, 89, 34, 545, 23, 57, 234, 464]
    print("\nResults for Maximum in List", maximum_list)
    print(maximum(maximum_list))
