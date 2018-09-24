# Instructions: Please Configure the Samples & Examples in your MySQL installation
# Open MySQL workbench and start localhost database
# Right-click on "world" and click "Set as Default Schema"
# Open this file in the Query window in the middle by clicking on the blue folder symbol


# 1. Which are the 3 countries with the highest population?
SELECT Name, Population FROM country ORDER BY Population DESC;

# 2. In how many countries Dutch is the main language?
SELECT COUNT(*) FROM countrylanguage WHERE `Language` = 'Dutch';

# 3. Which 5 countries have the highest percentage in Spanish speaking people?
SELECT CountryCode, Percentage FROM countrylanguage WHERE Language LIKE 'Spanish' ORDER BY Percentage DESC LIMIT 5;

# 4. What is the highest number of people speaking from countries having Spanish as official language?
SELECT * FROM country c JOIN countrylanguage cl on c.Code = cl.CountryCode
WHERE cl.IsOfficial = 'T' AND Language = 'Spanish' ORDER BY c.Population DESC;

# 5. Which countries have the most number of people living in mega cities (> 1 Mio.)?
SELECT country.Name,
SUM(city.Population) AS 'Population_City'
FROM city 
JOIN country on city.CountryCode = country.Code
WHERE city.Population > 1000000
GROUP BY CountryCode 
ORDER BY Population_City DESC;

# 6. How many cities are these for the before mentioned country with most people?
SELECT country.Name,
SUM(city.Population) AS 'Population_City',
COUNT(city.Population) AS 'Num_Megacities'
FROM city 
JOIN country on city.CountryCode = country.Code
WHERE city.Population > 1000000
GROUP BY CountryCode 
ORDER BY Population_City DESC;
