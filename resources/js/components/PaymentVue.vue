<template>
    <div v-if="canClient" class="card">

        <div v-if="!client.asaas_id">
            <div class="row pb-1 row-header">
                <div class="col-sm-12">
                    <h1>Pagamentos</h1>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 text-center alert alert-danger">
                    * Finalize o cadastro e libere todas funções de pagamento.
                </div>
            </div>
        </div>

        <div v-else>
            <div class="row pb-1 row-header">
                <div class="col-sm-6">
                    <h1>Pagamentos</h1>
                </div>
                <div class="col-sm-6 text-end">
                    <button type="button" class="btn btn-success" @click="createPayment">Realizar Pagamento</button>
                </div>
            </div>

            <table class="table table-hover">
                <thead>
                    <tr class="table-header">
                        <th class="text-center">Status</th>
                        <th>Produto</th>
                        <th>Valor</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center">Parcelas</th>
                        <th class="text-center">Realizado em</th>
                        <th class="text-center">Atualizado em</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="payment in payments" :key="payment.id">
                        <td :class="'text-center bg-status-'+payment.status">
                            {{ formatStatus(payment.status) }}
                        </td>
                        <td>{{ payment.description }}</td>
                        <td>{{ formatValue(payment.value) }}</td>
                        <td class="text-center">{{ formatType(payment.billing_type) }}</td>
                        <td class="text-center">{{ formatInstallment(payment.value, payment.billing_type, payment.installment, payment.installment_token) }}</td>
                        <td class="text-center">{{ payment.created_at }}</td>
                        <td class="text-center">{{ payment.updated_at }}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-primary btn-sm me-2" @click="showPayment(payment.id, payment.billing_type)">Visualizar</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="modal fade" id="modal-payment" tabindex="-1" aria-labelledby="modal-payment-label" aria-hidden="true">
                <div :class="'modal-dialog '+(is_holder == 'no' ? 'modal-lg' : '')">
                    <div class="modal-content">
                        <form @submit.prevent="submitPayment">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title" id="modal-payment-label">REALIZAR PAGAMENTO</h5>
                            </div>
                            <div class="modal-body">

                                <div class="row p-0">
                                    <div :class="(is_holder == 'no' ? 'col-sm-6 modal-payment-left' : 'col-sm-12')">

                                        <div class="form-group mb-2">
                                            <label for="value" >Qual produto deseja contratar?</label>
                                            <select class="form-control" id="value" v-model="payment_value">
                                                <option v-for="row in values" :value="row.value">{{row.name}}</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="type">Qual a forma de pagamento?</label>
                                            <select class="form-control" id="type" v-model="payment_type">
                                                <option v-for="row in types" :value="row.value">{{row.name}}</option>
                                            </select>
                                        </div>

                                        <div v-if="payment_type == 'CREDIT_CARD'">
                                            <div class="form-group mb-2">
                                                <label for="card">Selecione um cartão de crédito:</label>
                                                <select class="form-control" id="card" v-model="payment_card">
                                                    <option v-for="row in cards" :value="row.id">{{row.name}}</option>
                                                </select>
                                            </div>

                                            <div v-if="payment_value">
                                                <p class="text-success modal-subtitle mb-2 mt-3">Parcelas</p>

                                                <div class="form-group mb-2">
                                                    <label for="installment">Selecione a quantidade de parcelas:</label>
                                                    <select class="form-control" id="installment" v-model="payment_installment">
                                                        <option v-for="row in installments" :value="row.value">{{row.name}}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div v-if="!payment_card">
                                                <p class="text-success modal-subtitle mb-2 mt-3">Informe os dados do cartão</p>

                                                <div class="form-group mb-2">
                                                    <label for="number">Número do cartão:</label>
                                                    <input id="number" v-model="credit_card.number" type="number" class="form-control">
                                                </div>

                                                <div class="form-group mb-2">
                                                    <label for="holder_name">Nome impresso no cartão:</label>
                                                    <input id="holder_name" v-model="credit_card.holder_name" type="text" class="form-control">
                                                </div>

                                                <div class="row p-0">
                                                    <div class="form-group col-sm-4 mb-2 pl-0">
                                                        <label for="expiry_month">Mês Validade</label>
                                                        <input id="expiry_month" v-model="credit_card.expiry_month" type="number" class="form-control">
                                                    </div>

                                                    <div class="form-group col-sm-4 mb-2 pl-0 pr-0">
                                                        <label for="expiry_year">Ano Validade</label>
                                                        <input id="expiry_year" v-model="credit_card.expiry_year" type="number" class="form-control">
                                                    </div>

                                                    <div class="form-group col-sm-4 mb-2 pr-0">
                                                        <label for="ccv">Código (CVV)</label>
                                                        <input id="ccv" v-model="credit_card.ccv" type="number" class="form-control">
                                                    </div>
                                                </div>

                                                <div class="form-group mb-2">
                                                    <label for="is_holder">Você é o titular desse cartão?</label>
                                                    <select class="form-control" id="is_holder" v-model="is_holder">
                                                        <option value="yes">SIM</option>
                                                        <option value="no">NÃO</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div v-if="is_holder == 'no'" class="col-sm-6 modal-payment-right">

                                        <!-- Informações de Contato -->
                                        <p class="text-success modal-subtitle mb-2 mt-2">Informações do titular do cartão</p>

                                        <div class="form-group mb-2">
                                            <label for="holder_cpf_cnpj">CPF / CNPJ</label>
                                            <input v-model="holder.cpf_cnpj" id="holder_cpf_cnpj" type="number" class="form-control">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="holder_name">Nome</label>
                                            <input v-model="holder.name" id="holder_name" type="text" class="form-control">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="holder_email">E-mail</label>
                                            <input v-model="holder.email" id="holder_email" placeholder="Ex: exemplo@email.com" type="text" class="form-control">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="holder_phone">Celular</label>
                                            <input v-model="holder.phone" id="holder_phone" placeholder="Ex: 11911119999" type="number" class="form-control">
                                        </div>

                                        <!-- Endereço -->
                                        <p class="text-success modal-subtitle mb-2 mt-3">Endereço do titular do cartão</p>

                                        <div class="form-group mb-2">
                                            <label for="holder_postal_code">CEP</label>
                                            <input v-model="holder.postal_code" id="holder_postal_code" placeholder="Ex: 11111999" type="number" class="form-control">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="holder_address">Endereço</label>
                                            <input v-model="holder.address" id="holder_address" type="text" class="form-control">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="holder_province">Bairro</label>
                                            <input v-model="holder.province" id="holder_province" type="text" class="form-control">
                                        </div>

                                        <div class="row p-0">
                                            <div class="form-group col-sm-6 mb-2 pl-0">
                                                <label for="holder_address_number">Número</label>
                                                <input v-model="holder.address_number" id="holder_address_number" type="text" class="form-control">
                                            </div>
                                            <div class="form-group col-sm-6 mb-2 pr-0">
                                                <label for="holder_complement">Complemento</label>
                                                <input v-model="holder.complement" id="holder_complement" type="text" class="form-control">
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button v-if="!loading" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button v-if="!loading" type="submit" class="btn btn-success">
                                    <span v-if="payment_type == 'PIX'">Gerar QRCode para pagamento</span>
                                    <span v-else-if="payment_type == 'BOLETO'">Emitir boleto para pagamento</span>
                                    <span v-else>Finalizar Pagamento</span>
                                </button>
                                <div v-if="loading" class="btn btn-secondary">
                                    Processando pagamento...
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modal-pix" tabindex="-1" aria-labelledby="modal-pix-label" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="modal-pix-label">PIX - FINALIZE O PAGAMENTO</h5>
                        </div>
                        <div class="modal-body text-center">
                            <p class="mb-0">Acesse seu APP de pagamentos e faça a leitura do QR Code abaixo para efetuar o pagamento de forma rápida e segura.</p>
                            <img :src="'data:image/jpeg;base64,'+modal.pix.encodedImage">
                            <p>Ou copie e cole o código abaixo:</p>
                            <p id="copy" class="alert alert-secondary">{{modal.pix.payload}}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button id="btnCopy" @click="copyText" type="button" class="btn btn-success">Copiar código</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modal-boleto" tabindex="-1" aria-labelledby="modal-boleto-label" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white" style="border-bottom: 0;">
                            <h5 class="modal-title" id="modal-boleto-label">BOLETO - FINALIZE O PAGAMENTO</h5>
                        </div>
                        <div class="modal-body text-center p-0">
                            <iframe :src="modal.boleto" width="100%" height="450px"></iframe>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <a :href="modal.boleto" :title="modal.boleto" target="_blank" class="btn btn-success">Acessar link do boleto</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modal-credit_card" tabindex="-1" aria-labelledby="modal-credit_card-label" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white" style="border-bottom: 0;">
                            <h5 class="modal-title" id="modal-credit_card-label">CARTÃO DE CRÉDITO - PARCELAS</h5>
                        </div>
                        <div class="modal-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr class="table-header">
                                        <th class="text-center">Status</th>
                                        <th>Descrição</th>
                                        <th class="text-center">Valor</th>
                                        <th class="text-center">Vencimento</th>
                                        <th class="text-center">Cartão Utilizado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="payment in modal.credit_card" :key="payment.id">
                                        <td :class="'text-center bg-status-'+payment.status">
                                            {{ formatStatus(payment.status) }}
                                        </td>
                                        <td>{{(!payment.installmentNumber ? 'À Vista. ' : '')+payment.description}}</td>
                                        <td class="text-center">{{ formatValue(payment.value) }}</td>
                                        <td class="text-center">{{ formatDueDate(payment.dueDate) }}</td>
                                        <td class="text-center">
                                            {{ formatCard(payment.creditCard.creditCardNumber, payment.creditCard.creditCardBrand) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import bus from '../eventBus';
import _ from 'lodash';

export default {
    name: 'PaymentVue',
    data() {
        return {
            loading: false,

            // Cliente
            canClient: false,
            client: {
                id: '',
                asaas_id: '',
            },

            // Listagem dos pagamentos
            payments: [],

            // Modal realizar pagamento
            values: [],
            types: [],
            cards: [],
            installments: [],

            payment_value: '',
            payment_type: '',
            payment_card: '',
            payment_installment: '',

            is_holder: '',

            holder: {},
            credit_card: {},

            // Modal exibir pagamento
            modal: {
                pix: {
                    encodedImage: '',
                    payload: ''
                },
                boleto: '',
                credit_card: []
            }
        }
    },
    created() {
        bus.$on('set-client', (client) => {
            this.setClient(client);
        });
    },
    watch: {
        payment_value: _.debounce(function() {
            if(!this.payment_value){
                this.payment_installment = this.getDefaultData('payment_installment');
            }
            this.fetchInstallments();
        }),
        payment_type: _.debounce(function() {
            this.payment_card        = this.getDefaultData('payment_card');
            this.payment_installment = this.getDefaultData('payment_installment');
            this.credit_card         = this.getDefaultData('credit_card');
            this.is_holder           = this.getDefaultData('is_holder');
        }),
        payment_card: _.debounce(function() {
            this.credit_card  = this.getDefaultData('credit_card');
            this.is_holder    = this.getDefaultData('is_holder');
        }),
        is_holder: _.debounce(function() {
            this.holder = this.getDefaultData('holder');
        })
    },
    methods: {
        getDefaultData(key){
            let data = {
                values: [],
                types: [],
                cards: [],
                installments: [],

                payment_value: '',
                payment_type: '',
                payment_card: '',
                payment_installment: '',

                is_holder: 'yes',

                holder: {
                    cpf_cnpj: '',
                    name: '',
                    email: '',
                    phone: '',

                    postal_code: '',
                    address: '',
                    address_number: '',
                    complement: '',
                    province: ''
                },

                credit_card: {
                    holder_name: '',
                    number: '',
                    expiry_month: '',
                    expiry_year: '',
                    ccv: ''
                },
            };
            return data[key];
        },

        // Atualização do cliente
        setClient(client){
            this.canClient = (client ? true : false);
            this.client = client;

            if(this.canClient && this.client.asaas_id){
                this.fetchPayments();
            }
        },

        // Operações de pagamento
        createPayment() {
            this.loading = false;
            this.fetchValues();
            this.fetchTypes();
            this.fetchCards();
            this.installments          = this.getDefaultData('installments');
            this.payment_value         = this.getDefaultData('payment_value');
            this.payment_type          = this.getDefaultData('payment_type');
            this.payment_card          = this.getDefaultData('payment_card');
            this.payment_installment   = this.getDefaultData('payment_installment');
            this.is_holder             = this.getDefaultData('is_holder');
            this.holder                = this.getDefaultData('holder');
            this.credit_card           = this.getDefaultData('credit_card');
            this.modalOpen('payment');
        },
        submitPayment() {
            this.loading = true;

            let data = {
                client       : this.client,
                value_name   : this.getValueDescription(this.payment_value),
                value        : this.payment_value,
                type         : this.payment_type,
                card         : this.payment_card,
                installment  : this.payment_installment,
                is_holder    : this.is_holder,
                holder       : this.holder,
                credit_card  : this.credit_card,
            }

            axios.post('/api/payments', data)
                .then(response => {
                    this.modalClose('payment');
                    this.showPayment(response.data.id, response.data.billing_type);
                    this.fetchPayments();
                })
                .catch(error => {
                    this.loading = false;
                    let response = error.response;
                    switch (response.status){
                        case 422:
                            alert('VERIFIQUE OS ERROS NO FORMULÁRIO:\n\n'+response.data);
                            break;

                        default:
                            alert(response.data);
                    }
                });
        },
        showPayment(id, type){
            axios.get('/api/payments/'+id)
                .then(response => {
                    switch (type){
                        case 'PIX':
                            this.modal.pix = response.data;
                            this.modalOpen('pix');
                            break;
                        case 'BOLETO':
                            this.modal.boleto = response.data;
                            this.modalOpen('boleto');
                            break;
                        case 'CREDIT_CARD':
                            this.modal.credit_card = response.data;
                            this.modalOpen('credit_card');
                    }
                })
                .catch(error => {
                    let response = error.response;
                    alert(response.data);
                });
        },

        // Atualizar dados
        fetchPayments(){
            axios.get('/api/clients/'+this.client.id+'/payments')
                .then(response => {
                    this.payments = response.data;
                });
        },
        fetchValues(){
            let data = [];
            data.push({value: '', name: 'Selecione', description: ''});
            data.push({value: '1200.00', name: 'R$ 1.200,00 - MÓDULO CRM'        , description: 'MÓDULO CRM'});
            data.push({value: '2600.00', name: 'R$ 2.600,00 - MÓDULO RH'         , description: 'MÓDULO RH'});
            data.push({value: '3000.00', name: 'R$ 3.000,00 - MÓDULO FINANCEIRO' , description: 'MÓDULO FINANCEIRO'});
            data.push({value: '1500.00', name: 'R$ 1.500,00 - MÓDULO MARKETING'  , description: 'MÓDULO MARKETING'});
            data.push({value: '1000.00', name: 'R$ 1.000,00 - MÓDULO TREINAMENTO', description: 'MÓDULO TREINAMENTO'});
            this.values = data;
        },
        fetchTypes(){
            let data = [];
            data.push({value: ''           , name: 'Selecione'});
            data.push({value: 'PIX'        , name: 'PIX'});
            data.push({value: 'BOLETO'     , name: 'BOLETO'});
            data.push({value: 'CREDIT_CARD', name: 'CARTÃO DE CRÉDITO'});
            this.types = data;
        },
        fetchCards(){
            axios.get('/api/clients/'+this.client.id+'/cards')
                .then(response => {
                    let data = response.data;
                    data.unshift({id: '', name: 'Informar um novo cartão'});
                    this.cards = data;
                });
        },
        fetchInstallments(){
            let data = [];
            if(this.payment_value){
                let value = parseFloat(this.payment_value);
                let valueFormat = this.formatValue(value);

                data.push({value: '', name: 'À Vista ' + valueFormat});

                let iFormat = '';
                for (let i = 2; i <= 12; i++) {
                    valueFormat = (value / parseFloat(i));
                    valueFormat = this.formatValue(valueFormat);
                    iFormat = i.toLocaleString('pt-BR', {minimumIntegerDigits: 2, minimumFractionDigits: 0});

                    data.push({value: i, name: iFormat + ' x ' + valueFormat});
                }
            }
            this.installments = data;
        },

        // Obter dados
        getValueDescription(value){
            let description = '';
            this.values.forEach(function(data) {
                if(data.value == value){
                    description = data.description;
                }
            });
            return description;
        },

        // Modal
        modalOpen(modal){
            $('#modal-'+modal).modal('show');
        },
        modalClose(modal){
            $('#modal-'+modal).modal('hide');
        },

        // Formatações
        formatStatus(status, title = false){
            switch (status){
                case 'PENDING'  : return !title ? 'PENDENTE'   : 'Aguardando pagamento';
                case 'CONFIRMED': return !title ? 'CONFIRMADO' : 'Pagamento confirmado';
                default: return status;
            }
        },
        formatType(type){
            return type === 'CREDIT_CARD' ? 'CARTÃO' : type;
        },
        formatValue(value){
            value = parseFloat(value);
            return value.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
        },
        formatDueDate(dueDate){
            dueDate = dueDate.split('-');
            return dueDate[2] + '/' + dueDate[1] + '/' + dueDate[0];
        },
        formatCard(creditCardNumber, creditCardBrand){
            if(creditCardBrand == 'UNKNOWN'){
                creditCardBrand = '';
            }
            return '('+creditCardNumber+') '+creditCardBrand;
        },
        formatInstallment(value, type, installment, installment_token){
            if(type !== 'CREDIT_CARD'){
                return '---';
            }
            if(!installment){
                return 'À Vista';
            }
            return installment+' de '+this.formatValue(value/installment);
        },

        // Metódos auxiliares
        copyText() {
            let texto = document.getElementById("copy").innerText;
            let btn   = document.getElementById("btnCopy");
            let item  = new ClipboardItem({
                "text/plain": new Blob([texto], { type: "text/plain" })
            });
            navigator.clipboard.write([item]);
            btn.innerText = "Copiado com sucesso";
            setTimeout(function() {btn.innerText = "Copiar código";}, 1500);
        }
    }
};
</script>

<style scoped>
    .table-header{
        background: #E9ECEF;
    }
    .modal-title{
        font-weight: 500;
        font-size: 16pt;
    }
    .modal-subtitle{
        font-weight: bold;
        text-transform: uppercase;
    }
    .modal-payment-left{
        padding-right: 1rem;
        border-right: 1px dotted rgba(0, 0, 0, 0.175);
    }
    .modal-payment-right{
        padding-left: 1rem;
    }
    .bg-status-PENDING{
        background: #ffcc80;
    }
    .bg-status-CONFIRMED{
        background: #a5d6a7;
    }
</style>
