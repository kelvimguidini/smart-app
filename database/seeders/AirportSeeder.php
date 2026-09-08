<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AirportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $airports = [
            // Brasil - Principais Hubs e Capitais
            ['iata_code' => 'NVT', 'name' => 'Aeroporto Internacional de Navegantes - Ministro Victor Konder', 'city' => 'Navegantes', 'state' => 'SC', 'country' => 'Brasil'],
            ['iata_code' => 'GIG', 'name' => 'Aeroporto Internacional Tom Jobim - Galeão', 'city' => 'Rio de Janeiro', 'state' => 'RJ', 'country' => 'Brasil'],
            ['iata_code' => 'SDU', 'name' => 'Aeroporto Santos Dumont', 'city' => 'Rio de Janeiro', 'state' => 'RJ', 'country' => 'Brasil'],
            ['iata_code' => 'GRU', 'name' => 'Aeroporto Internacional de Guarulhos / Cumbica', 'city' => 'Guarulhos', 'state' => 'SP', 'country' => 'Brasil'],
            ['iata_code' => 'CGH', 'name' => 'Aeroporto de Congonhas', 'city' => 'São Paulo', 'state' => 'SP', 'country' => 'Brasil'],
            ['iata_code' => 'VCP', 'name' => 'Aeroporto Internacional de Viracopos', 'city' => 'Campinas', 'state' => 'SP', 'country' => 'Brasil'],
            ['iata_code' => 'BSB', 'name' => 'Aeroporto Internacional Presidente Juscelino Kubitschek', 'city' => 'Brasília', 'state' => 'DF', 'country' => 'Brasil'],
            ['iata_code' => 'CNF', 'name' => 'Aeroporto Internacional de Belo Horizonte / Confins', 'city' => 'Belo Horizonte', 'state' => 'MG', 'country' => 'Brasil'],
            ['iata_code' => 'PLU', 'name' => 'Aeroporto da Pampulha', 'city' => 'Belo Horizonte', 'state' => 'MG', 'country' => 'Brasil'],
            ['iata_code' => 'SSA', 'name' => 'Aeroporto Internacional Deputado Luís Eduardo Magalhães', 'city' => 'Salvador', 'state' => 'BA', 'country' => 'Brasil'],
            ['iata_code' => 'REC', 'name' => 'Aeroporto Internacional dos Guararapes - Gilberto Freyre', 'city' => 'Recife', 'state' => 'PE', 'country' => 'Brasil'],
            ['iata_code' => 'FOR', 'name' => 'Aeroporto Internacional Pinto Martins', 'city' => 'Fortaleza', 'state' => 'CE', 'country' => 'Brasil'],
            ['iata_code' => 'CWB', 'name' => 'Aeroporto Internacional Afonso Pena', 'city' => 'Curitiba', 'state' => 'PR', 'country' => 'Brasil'],
            ['iata_code' => 'POA', 'name' => 'Aeroporto Internacional Salgado Filho', 'city' => 'Porto Alegre', 'state' => 'RS', 'country' => 'Brasil'],
            ['iata_code' => 'FLN', 'name' => 'Aeroporto Internacional Hercílio Luz', 'city' => 'Florianópolis', 'state' => 'SC', 'country' => 'Brasil'],
            ['iata_code' => 'VIX', 'name' => 'Aeroporto de Vitória - Eurico de Aguiar Salles', 'city' => 'Vitória', 'state' => 'ES', 'country' => 'Brasil'],
            ['iata_code' => 'GYN', 'name' => 'Aeroporto Santa Genoveva', 'city' => 'Goiânia', 'state' => 'GO', 'country' => 'Brasil'],
            ['iata_code' => 'CGB', 'name' => 'Aeroporto Internacional Marechal Rondon', 'city' => 'Cuiabá', 'state' => 'MT', 'country' => 'Brasil'],
            ['iata_code' => 'CGR', 'name' => 'Aeroporto Internacional de Campo Grande', 'city' => 'Campo Grande', 'state' => 'MS', 'country' => 'Brasil'],
            ['iata_code' => 'BEL', 'name' => 'Aeroporto Internacional de Belém / Val-de-Cans', 'city' => 'Belém', 'state' => 'PA', 'country' => 'Brasil'],
            ['iata_code' => 'MAO', 'name' => 'Aeroporto Internacional Eduardo Gomes', 'city' => 'Manaus', 'state' => 'AM', 'country' => 'Brasil'],
            ['iata_code' => 'NAT', 'name' => 'Aeroporto Internacional de São Gonçalo do Amarante - Governador Aluízio Alves', 'city' => 'Natal', 'state' => 'RN', 'country' => 'Brasil'],
            ['iata_code' => 'MCZ', 'name' => 'Aeroporto Internacional Zumbi dos Palmares', 'city' => 'Maceió', 'state' => 'AL', 'country' => 'Brasil'],
            ['iata_code' => 'JPA', 'name' => 'Aeroporto Internacional Presidente Castro Pinto', 'city' => 'João Pessoa', 'state' => 'PB', 'country' => 'Brasil'],
            ['iata_code' => 'AJU', 'name' => 'Aeroporto Internacional de Aracaju - Santa Maria', 'city' => 'Aracaju', 'state' => 'SE', 'country' => 'Brasil'],
            ['iata_code' => 'THE', 'name' => 'Aeroporto Senador Petrônio Portella', 'city' => 'Teresina', 'state' => 'PI', 'country' => 'Brasil'],
            ['iata_code' => 'SLZ', 'name' => 'Aeroporto Internacional Marechal Cunha Machado', 'city' => 'São Luís', 'state' => 'MA', 'country' => 'Brasil'],
            ['iata_code' => 'PMW', 'name' => 'Aeroporto de Palmas - Brigadeiro Lysias Rodrigues', 'city' => 'Palmas', 'state' => 'TO', 'country' => 'Brasil'],
            ['iata_code' => 'PVH', 'name' => 'Aeroporto Internacional Governador Jorge Teixeira de Oliveira', 'city' => 'Porto Velho', 'state' => 'RO', 'country' => 'Brasil'],
            ['iata_code' => 'RBR', 'name' => 'Aeroporto Internacional Plácido de Castro', 'city' => 'Rio Branco', 'state' => 'AC', 'country' => 'Brasil'],
            ['iata_code' => 'BVB', 'name' => 'Aeroporto Internacional de Boa Vista - Atlas Brasil Cantanhede', 'city' => 'Boa Vista', 'state' => 'RR', 'country' => 'Brasil'],
            ['iata_code' => 'MCP', 'name' => 'Aeroporto Internacional Alberto Alcolumbre', 'city' => 'Macapá', 'state' => 'AP', 'country' => 'Brasil'],

            // Brasil - Aeroportos Regionais e Turísticos Relevantes
            ['iata_code' => 'IGU', 'name' => 'Aeroporto Internacional de Foz do Iguaçu', 'city' => 'Foz do Iguaçu', 'state' => 'PR', 'country' => 'Brasil'],
            ['iata_code' => 'BPS', 'name' => 'Aeroporto de Porto Seguro', 'city' => 'Porto Seguro', 'state' => 'BA', 'country' => 'Brasil'],
            ['iata_code' => 'IOS', 'name' => 'Aeroporto Jorge Amado', 'city' => 'Ilhéus', 'state' => 'BA', 'country' => 'Brasil'],
            ['iata_code' => 'UDI', 'name' => 'Aeroporto Tenente Coronel Aviador César Bombonato', 'city' => 'Uberlândia', 'state' => 'MG', 'country' => 'Brasil'],
            ['iata_code' => 'RAO', 'name' => 'Aeroporto Leite Lopes', 'city' => 'Ribeirão Preto', 'state' => 'SP', 'country' => 'Brasil'],
            ['iata_code' => 'SJP', 'name' => 'Aeroporto Professor Eribelto Manoel Reino', 'city' => 'São José do Rio Preto', 'state' => 'SP', 'country' => 'Brasil'],
            ['iata_code' => 'SJK', 'name' => 'Aeroporto Professor Urbano Ernesto Stumpf', 'city' => 'São José dos Campos', 'state' => 'SP', 'country' => 'Brasil'],
            ['iata_code' => 'JOI', 'name' => 'Aeroporto Lauro Carneiro de Loyola', 'city' => 'Joinville', 'state' => 'SC', 'country' => 'Brasil'],
            ['iata_code' => 'XAP', 'name' => 'Aeroporto Serafin Enoss Bertaso', 'city' => 'Chapecó', 'state' => 'SC', 'country' => 'Brasil'],
            ['iata_code' => 'CXJ', 'name' => 'Aeroporto Hugo Cantergiani', 'city' => 'Caxias do Sul', 'state' => 'RS', 'country' => 'Brasil'],
            ['iata_code' => 'PET', 'name' => 'Aeroporto Internacional João Simões Lopes Neto', 'city' => 'Pelotas', 'state' => 'RS', 'country' => 'Brasil'],
            ['iata_code' => 'SMT', 'name' => 'Aeroporto de Santa Maria', 'city' => 'Santa Maria', 'state' => 'RS', 'country' => 'Brasil'],
            ['iata_code' => 'MGF', 'name' => 'Aeroporto Regional Silvio Name Junior', 'city' => 'Maringá', 'state' => 'PR', 'country' => 'Brasil'],
            ['iata_code' => 'LDB', 'name' => 'Aeroporto Governador José Richa', 'city' => 'Londrina', 'state' => 'PR', 'country' => 'Brasil'],
            ['iata_code' => 'CAC', 'name' => 'Aeroporto Coronel Adalberto Mendes da Silva', 'city' => 'Cascavel', 'state' => 'PR', 'country' => 'Brasil'],
            ['iata_code' => 'JDO', 'name' => 'Aeroporto Orlando Bezerra de Menezes', 'city' => 'Juazeiro do Norte', 'state' => 'CE', 'country' => 'Brasil'],
            ['iata_code' => 'CPV', 'name' => 'Aeroporto Presidente João Suassuna', 'city' => 'Campina Grande', 'state' => 'PB', 'country' => 'Brasil'],
            ['iata_code' => 'PNZ', 'name' => 'Aeroporto Senador Nilo Coelho', 'city' => 'Petrolina', 'state' => 'PE', 'country' => 'Brasil'],
            ['iata_code' => 'FEN', 'name' => 'Aeroporto Governador Carlos Wilson', 'city' => 'Fernando de Noronha', 'state' => 'PE', 'country' => 'Brasil'],
            ['iata_code' => 'CFB', 'name' => 'Aeroporto Internacional de Cabo Frio', 'city' => 'Cabo Frio', 'state' => 'RJ', 'country' => 'Brasil'],
            ['iata_code' => 'MEA', 'name' => 'Aeroporto de Macaé', 'city' => 'Macaé', 'state' => 'RJ', 'country' => 'Brasil'],
            ['iata_code' => 'JTC', 'name' => 'Aeroporto Estadual Moussa Nakhl Tobias', 'city' => 'Bauru', 'state' => 'SP', 'country' => 'Brasil'],
            ['iata_code' => 'PPB', 'name' => 'Aeroporto Estadual Adhemar de Barros', 'city' => 'Presidente Prudente', 'state' => 'SP', 'country' => 'Brasil'],
            ['iata_code' => 'ARU', 'name' => 'Aeroporto Estadual de Araçatuba', 'city' => 'Araçatuba', 'state' => 'SP', 'country' => 'Brasil'],
            ['iata_code' => 'MOC', 'name' => 'Aeroporto Mário Ribeiro', 'city' => 'Montes Claros', 'state' => 'MG', 'country' => 'Brasil'],
            ['iata_code' => 'IZA', 'name' => 'Aeroporto Regional da Zona da Mata', 'city' => 'Juiz de Fora', 'state' => 'MG', 'country' => 'Brasil'],
            ['iata_code' => 'IPN', 'name' => 'Aeroporto Usiminas', 'city' => 'Ipatinga', 'state' => 'MG', 'country' => 'Brasil'],
            ['iata_code' => 'OPS', 'name' => 'Aeroporto Presidente João Batista Figueiredo', 'city' => 'Sinop', 'state' => 'MT', 'country' => 'Brasil'],
            ['iata_code' => 'ROO', 'name' => 'Aeroporto Maestro Marinho Franco', 'city' => 'Rondonópolis', 'state' => 'MT', 'country' => 'Brasil'],
            ['iata_code' => 'BYO', 'name' => 'Aeroporto Regional de Bonito', 'city' => 'Bonito', 'state' => 'MS', 'country' => 'Brasil'],
            ['iata_code' => 'STM', 'name' => 'Aeroporto Internacional Maestro Wilson Fonseca', 'city' => 'Santarém', 'state' => 'PA', 'country' => 'Brasil'],
            ['iata_code' => 'MAB', 'name' => 'Aeroporto João Correa da Rocha', 'city' => 'Marabá', 'state' => 'PA', 'country' => 'Brasil'],
            ['iata_code' => 'IMP', 'name' => 'Aeroporto Prefeito Renato Moreira', 'city' => 'Imperatriz', 'state' => 'MA', 'country' => 'Brasil'],
            ['iata_code' => 'JJD', 'name' => 'Aeroporto Regional Comandante Ariston Pessoa', 'city' => 'Jericoacoara', 'state' => 'CE', 'country' => 'Brasil'],

            // Internacionais - Principais Conexões & Destinos Frequentes
            ['iata_code' => 'MIA', 'name' => 'Miami International Airport', 'city' => 'Miami', 'state' => 'FL', 'country' => 'Estados Unidos'],
            ['iata_code' => 'MCO', 'name' => 'Orlando International Airport', 'city' => 'Orlando', 'state' => 'FL', 'country' => 'Estados Unidos'],
            ['iata_code' => 'JFK', 'name' => 'John F. Kennedy International Airport', 'city' => 'New York', 'state' => 'NY', 'country' => 'Estados Unidos'],
            ['iata_code' => 'EWR', 'name' => 'Newark Liberty International Airport', 'city' => 'Newark', 'state' => 'NJ', 'country' => 'Estados Unidos'],
            ['iata_code' => 'LAX', 'name' => 'Los Angeles International Airport', 'city' => 'Los Angeles', 'state' => 'CA', 'country' => 'Estados Unidos'],
            ['iata_code' => 'LIS', 'name' => 'Aeroporto Humberto Delgado', 'city' => 'Lisboa', 'state' => null, 'country' => 'Portugal'],
            ['iata_code' => 'OPO', 'name' => 'Aeroporto Francisco Sá Carneiro', 'city' => 'Porto', 'state' => null, 'country' => 'Portugal'],
            ['iata_code' => 'MAD', 'name' => 'Aeropuerto Adolfo Suárez Madrid-Barajas', 'city' => 'Madrid', 'state' => null, 'country' => 'Espanha'],
            ['iata_code' => 'BCN', 'name' => 'Aeropuerto Josep Tarradellas Barcelona-El Prat', 'city' => 'Barcelona', 'state' => null, 'country' => 'Espanha'],
            ['iata_code' => 'CDG', 'name' => 'Aéroport Charles de Gaulle', 'city' => 'Paris', 'state' => null, 'country' => 'França'],
            ['iata_code' => 'ORY', 'name' => 'Aéroport de Paris-Orly', 'city' => 'Paris', 'state' => null, 'country' => 'França'],
            ['iata_code' => 'LHR', 'name' => 'Heathrow Airport', 'city' => 'Londres', 'state' => null, 'country' => 'Reino Unido'],
            ['iata_code' => 'FRA', 'name' => 'Frankfurt Airport', 'city' => 'Frankfurt', 'state' => null, 'country' => 'Alemanha'],
            ['iata_code' => 'FCO', 'name' => 'Aeroporto Leonardo da Vinci - Fiumicino', 'city' => 'Roma', 'state' => null, 'country' => 'Itália'],
            ['iata_code' => 'AMS', 'name' => 'Amsterdam Airport Schiphol', 'city' => 'Amsterdã', 'state' => null, 'country' => 'Holanda'],
            ['iata_code' => 'EZE', 'name' => 'Aeropuerto Internacional Ministro Pistarini (Ezeiza)', 'city' => 'Buenos Aires', 'state' => null, 'country' => 'Argentina'],
            ['iata_code' => 'AEP', 'name' => 'Aeroparque Jorge Newbery', 'city' => 'Buenos Aires', 'state' => null, 'country' => 'Argentina'],
            ['iata_code' => 'SCL', 'name' => 'Aeropuerto Internacional Arturo Merino Benítez', 'city' => 'Santiago', 'state' => null, 'country' => 'Chile'],
            ['iata_code' => 'MVD', 'name' => 'Aeropuerto Internacional de Carrasco', 'city' => 'Montevidéu', 'state' => null, 'country' => 'Uruguai'],
            ['iata_code' => 'PDP', 'name' => 'Aeropuerto Internacional de Laguna del Sauce', 'city' => 'Punta del Este', 'state' => null, 'country' => 'Uruguai'],
            ['iata_code' => 'BOG', 'name' => 'Aeropuerto Internacional El Dorado', 'city' => 'Bogotá', 'state' => null, 'country' => 'Colômbia'],
            ['iata_code' => 'LIM', 'name' => 'Aeropuerto Internacional Jorge Chávez', 'city' => 'Lima', 'state' => null, 'country' => 'Peru'],
            ['iata_code' => 'PTY', 'name' => 'Aeropuerto Internacional de Tocumen', 'city' => 'Cidade do Panamá', 'state' => null, 'country' => 'Panamá'],
            ['iata_code' => 'CUN', 'name' => 'Aeropuerto Internacional de Cancún', 'city' => 'Cancún', 'state' => null, 'country' => 'México'],
            ['iata_code' => 'DXB', 'name' => 'Dubai International Airport', 'city' => 'Dubai', 'state' => null, 'country' => 'Emirados Árabes Unidos'],
        ];

        foreach ($airports as $airport) {
            $exists = DB::table('airfare_airports')
                ->where('iata_code', $airport['iata_code'])
                ->exists();

            if (!$exists) {
                DB::table('airfare_airports')->insert([
                    'iata_code' => $airport['iata_code'],
                    'name' => $airport['name'],
                    'city' => $airport['city'],
                    'state' => $airport['state'],
                    'country' => $airport['country'],
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
