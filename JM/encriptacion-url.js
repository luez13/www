const jsonData = {
    cedula: 'V-28422154',
    documento: {
        tipo: 'titulo',
        categoria: 'ingeniería',
        pnf: 'informática'
    }
}

// Creo un montón de datos sobre identificación y títulos.

const jsonString = JSON.stringify(jsonData)

// ¡Ahora hago que toda esta información sea legible para las máquinas codificándola como un string!

const encodedData = btoa(jsonString)

// Envuelvo ese montón de texto codificado con Base64 para que se vea como un montón de letras extrañas.

const url = `https://uptaivirtualsarec.com/saeyce/horarios-main/cer/index.php?data=${encodeURIComponent(encodedData)}`

// Mezclo todo esto en una dirección web complicada, haciéndola parecer muy ocupada y misteriosa con un parámetro de datos.

const params = new URLSearchParams(new URL(url).search)

// Ahora tomo esa dirección web, la abro y rebusco entre sus cosas para encontrar el parámetro de datos.

const datas = atob(params.get('data'))

// Finalmente, desenredo toda esa información codificada, devolviéndola a su forma original para que pueda leerla como el montón de datos que era.

console.log(datas)

// Y aquí, ¡observo cómo era toda esa información de identificación y títulos que antes estaba escondida!
