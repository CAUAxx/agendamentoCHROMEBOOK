document.addEventListener('DOMContentLoaded', function () {

    // Função para sanitizar entradas
    function escapeHTML(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    const monthsBR = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
    const tableDays = document.getElementById('dias');

    function formatMonth(mes) {
        return mes < 10 ? '0' + mes.toString() : mes.toString();
    }

    function GetDaysCalendar(mes, ano) {
        document.getElementById('mes').innerHTML = escapeHTML(monthsBR[mes]);
        document.getElementById('ano').innerHTML = escapeHTML(ano.toString());

        let firstDayOfWeek = new Date(ano, mes, 1).getDay() - 1;
        if (firstDayOfWeek < 0) firstDayOfWeek = 6; // Corrige o caso quando firstDayOfWeek é -1
        let getLastDayThisMonth = new Date(ano, mes + 1, 0).getDate();

        const dayCells = tableDays.getElementsByTagName('td');

        for (let i = -firstDayOfWeek, index = 0; index < dayCells.length; i++, index++) {
            let dt = new Date(ano, mes, i);
            let dtNow = new Date();
            let dayTable = dayCells[index];

            dayTable.className = ''; // Resetando classes
            dayTable.innerHTML = ''; // Resetando conteúdo

            if (i >= 1 && i <= getLastDayThisMonth) {
                dayTable.innerHTML = escapeHTML(i.toString());
                dayTable.addEventListener('click', function () {
                    const selected = tableDays.querySelector('.dia-selecionado');
                    if (selected) {
                        selected.classList.remove('dia-selecionado');
                        selected.removeAttribute('value');
                    }
                    this.classList.add('dia-selecionado');
                    this.setAttribute('value', escapeHTML(`${formatMonth(i)}-${formatMonth(mes + 1)}-${ano}`));
                });

                if (dt.getFullYear() === dtNow.getFullYear() && dt.getMonth() === dtNow.getMonth() && dt.getDate() === dtNow.getDate()) {
                    dayTable.classList.add('dia-atual');
                }
            }
        }
    }

    let now = new Date();
    let mes = now.getMonth();
    let ano = now.getFullYear();
    GetDaysCalendar(mes, ano);

    document.getElementById('btn_next').addEventListener('click', function () {
        mes++;
        if (mes > 11) {
            mes = 0;
            ano++;
        }
        GetDaysCalendar(mes, ano);
    });

    document.getElementById('btn_prev').addEventListener('click', function () {
        mes--;
        if (mes < 0) {
            mes = 11;
            ano--;
        }
        GetDaysCalendar(mes, ano);
    });

    const horariosManha = ['7:10', '8:00', '8:50', '9:50', '10:40', '11:30'];
    const horariosNoite = ['18:00', '18:40', '19:20', '20:00', '20:50', '21:30'];

    let selectedHorarios = [];

    function makeSelectable() {
        const turno = document.getElementById('turno');
        const pElements = turno.getElementsByTagName('p');

        for (let i = 0; i < pElements.length; i++) {
            pElements[i].className = 'turno';
            pElements[i].addEventListener('click', function () {
                for (let j = 0; j < pElements.length; j++) {
                    pElements[j].classList.remove('selected');
                }
                this.classList.add('selected');
                mudarHorarios(this.id);
            });
        }
    }

    function mudarHorarios(turno) {
        selectedHorarios = [];
        let horarios;
        if (turno === 'manha') {
            horarios = horariosManha;
        } else if (turno === 'noite') {
            horarios = horariosNoite;
        }

        const horas = document.querySelector('.horas');
        while (horas.firstChild) {
            horas.removeChild(horas.firstChild);
        }

        horarios.forEach(function (horario) {
            const div = document.createElement('div');
            div.textContent = escapeHTML(horario);
            div.className = 'horario';

            div.addEventListener('click', function () {
                if (div.classList.contains('selected')) {
                    div.classList.remove('selected');
                    const index = selectedHorarios.indexOf(horario);
                    if (index > -1) selectedHorarios.splice(index, 1);
                } else {
                    div.classList.add('selected');
                    selectedHorarios.push(horario);
                }
            });

            horas.appendChild(div);
        });
    }

    document.getElementById('submit').addEventListener('click', (submit) => {
        submit.preventDefault();

        const redirectLink = escapeHTML(document.getElementById("redirect").getAttribute("value"));
        const data = escapeHTML(document.getElementsByClassName("dia-selecionado")[0]?.getAttribute("value") || '');
        const preferencia = escapeHTML(document.getElementById("preferencia").value);

        const postData = `data=${encodeURIComponent(data)}&horarios=${encodeURIComponent(selectedHorarios.join(','))}&preferencia=${encodeURIComponent(preferencia)}`;

        const httpc = new XMLHttpRequest();
        httpc.open("POST", "createAgendamento.php", true);
        httpc.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        httpc.onreadystatechange = function () {
            if (httpc.readyState === 4 && httpc.status === 200) {
                alert(escapeHTML(httpc.responseText));
                window.location.replace(`AgendaSemanal.php${redirectLink}`);
            }
        };

        httpc.send(postData);
    });

    window.onload = function () {
        makeSelectable();
        mudarHorarios('manha');
    };
});
