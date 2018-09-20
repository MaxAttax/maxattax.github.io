# Task 1: Calculation of the Faculty through Python

def faculty_for(value):
    faculty = 1
    for index in range(1, value+1):
        faculty = faculty * index
    return faculty


def faculty_while(value):
    faculty = 1
    while value > 1:
        faculty = faculty * value
        value = value-1
    return faculty
	

def faculty_recursive(value):
    if value < 2:
        return 1
    else:
        return value * faculty_recursive(value-1)
	
	
if __name__ == '__main__':
    n = 6
    print("\nResults for Faculty of", n, ":")
    print("Faculty, calculated with for-loop:", faculty_for(n))
    print("Faculty, calculated with while-loop:", faculty_while(n))
    print("Faculty, calculated recursively:", faculty_recursive(n))
