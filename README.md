# Validación de muestreo via renaper
## Procesamiento de archivo excel
    La idea es que se tome como input un archivo excel, por cada fila se tome el DNI, el Sexo
    se valide contra el renaper que esa persona exista 
    si la persona existe, entonces tomar el valor de la fecha de nacimiento y compararla contra la que informa el renaper
    si la persona pasa esta ultima validación, entonces actualizar un campo validado que diga si
    si la persona existe en el renaper pero la fecha de nacimiento no concuerda, en el campo validado que diga no,
      en el campo siguiente el motivo, en este caso decir la fecha de nacimiento informada por renaper es tal, y
      la del excel informada es tal otra, no coinciden
    si la persona no existe en el renaper, entonces, en el campo validada poner no y en el campo siguiente el motivo:
      la persona no se pudo encontrar en el servicio de renaper con esta llamada, y ahi le pones la url con los parametros 
## Output
    La idea es que siempre expulse un nuevo archivo (el nombre con un hash o algo) para que tenga siempre el excel
      original    

