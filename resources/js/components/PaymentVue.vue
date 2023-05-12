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
            <table class="table">
                <thead>
                    <tr class="table-header">
                        <th>{{ labels[lang].id }}</th>
                        <th>{{ labels[lang].name }}</th>
                        <th>{{ labels[lang].category }}</th>
                        <th class="text-center">{{ labels[lang].created }}</th>
                        <th class="text-center">{{ labels[lang].updated }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in products" :key="product.id">
                        <td>{{ product.id }}</td>
                        <td>{{ product.name }}</td>
                        <td>{{ product.category.name }}</td>
                        <td class="text-center">{{ product.created_at }}</td>
                        <td class="text-center">{{ product.updated_at }}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-primary btn-sm me-2" @click="editProduct(product)">{{ labels[lang].edit }}</button>
                            <button type="button" class="btn btn-danger btn-sm" @click="deleteProduct(product)">{{ labels[lang].delete }}</button>
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
                                                    <option v-for="row in cards" :value="row.value">{{row.name}}</option>
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
                                                        <label for="ccv">Código (CCV)</label>
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
                                            <input v-model="holder.cpf_cnpj" id="holder_cpf_cnpj" placeholder="Ex: 11111111111 / 99999999999999" type="number" class="form-control">
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
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">
                                    <span v-if="payment_type == 'PIX'">Gerar QRCode para pagamento</span>
                                    <span v-else-if="payment_type == 'BOLETO'">Emitir boleto para pagamento</span>
                                    <span v-else>Finalizar Pagamento</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import bus from '../eventBus';
import _ from 'lodash';
import FormRow from "./forms/RowHorizontalVue.vue";

export default {
    name: 'PaymentVue',
    components: {FormRow},
    data() {
        return {
            // Cliente

            canClient: false,
            client: {
                asaas_id: '',
            },

            // Modal de Pagamentos

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







            categories: [],
            products: [],
            product: {
                id: null,
                name: '',
                category_id: null,
                created_at: '',
                updated_at: '',
            },
            formAction: '',
            formTitle: '',
            searchText: '',
            lang: 'en',
            labels: {
                en: {
                    products: 'Products',
                    addProduct: 'Add Product',
                    editProduct: 'Edit Product',

                    id: 'ID',
                    name: 'Name',
                    category: 'Category',
                    created: 'Created',
                    updated: 'Updated',

                    edit: 'Edit',
                    delete: 'Delete',
                    create: 'Create',
                    update: 'Update',
                    cancel: 'Cancel',

                    clearSearch: 'Clear search',

                    textSearch: 'Search by Name or Category...',
                    textConfirDelete: 'Are you sure you want to delete this product?',
                },
                pt: {
                    products: 'Produtos',
                    addProduct: 'Adicionar Produto',
                    editProduct: 'Editar Produto',

                    id: 'ID',
                    name: 'Nome',
                    category: 'Categoria',
                    created: 'Criado em',
                    updated: 'Atualizado em',

                    edit: 'Editar',
                    delete: 'Excluir',
                    create: 'Cadastrar',
                    update: 'Atualizar',
                    cancel: 'Cancelar',

                    clearSearch: 'Limpar busca',

                    textSearch: 'Buscar por Nome ou Categoria...',
                    textConfirDelete: 'Tem certeza de que deseja excluir este produto?',
                },
            },
        }
    },
    created() {
        bus.$on('set-client', (client) => {
            this.setClient(client);
        });

        this.fetchProducts();
        this.fetchCategories();

        bus.$on('category-created', () => {
            this.fetchCategories();
        });

        bus.$on('category-updated', () => {
            this.fetchCategories();
            this.fetchProducts();
        });

        bus.$on('category-deleted', () => {
            this.fetchCategories();
            this.fetchProducts();
        });

        bus.$on('category-search', (category) => {
            this.searchText = category;
        });

        bus.$on('languagem-select', (lang) => {
            this.fetchLang(lang);
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
        setClient(client){
            this.canClient = (client ? true : false);
            this.client = client;
        },
        getDefaultData(key){
            let data = {
                canClient: false,
                client: {
                    asaas_id: '',
                },

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
        modalOpen(modal){
            $('#modal-'+modal).modal('show');
        },
        modalClose(modal){
            $('#modal-'+modal).modal('hide');
        },

        createPayment() {
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

            let data = {
                client               : this.client,
                payment_value        : this.payment_value,
                payment_type         : this.payment_type,
                payment_card         : this.payment_card,
                payment_installment  : this.payment_installment,
                is_holder            : this.is_holder,
                holder               : this.holder,
                credit_card          : this.credit_card,
            }

            console.log(data);

            /*axios.post('/api/products', data)
                .then(response => {
                    this.modalClose('payment');
                    this.searchText = '';
                    this.fetchProducts();
                    this.emitProducts('created');
                })
                .catch(error => {
                    console.log(error);
                });*/
        },

        fetchValues(){
            let data = [];
            data.push({value: ''       , name: 'Selecione'});
            data.push({value: '1200.00', name: 'R$ 1.200,00 - MÓDULO CRM'});
            data.push({value: '2600.00', name: 'R$ 2.600,00 - MÓDULO RH'});
            data.push({value: '3000.00', name: 'R$ 3.000,00 - MÓDULO FINANCEIRO'});
            data.push({value: '1500.00', name: 'R$ 1.500,00 - MÓDULO MARKETING'});
            data.push({value: '1000.00', name: 'R$ 1.000,00 - MÓDULO TREINAMENTO'});
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
            let data = [];
            data.push({value: '' , name: 'Informar um novo cartão'});
            data.push({value: '1', name: '(8855) MASTERCARD'});
            data.push({value: '2', name: '(5522) VISA'});
            data.push({value: '3', name: '(4455) MASTERCARD'});
            this.cards = data;
        },
        fetchInstallments(){
            let data = [];
            if(this.payment_value){
                let value = parseFloat(this.payment_value);
                let valueFormat = value.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});

                data.push({value: '', name: 'À Vista ' + valueFormat});

                let iFormat = '';
                for (let i = 2; i <= 12; i++) {
                    valueFormat = (value / parseFloat(i));
                    valueFormat = valueFormat.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
                    iFormat = i.toLocaleString('pt-BR', {minimumIntegerDigits: 2, minimumFractionDigits: 0});

                    data.push({value: i, name: iFormat + ' x ' + valueFormat});
                }
            }
            this.installments = data;
        },








        emitProducts(event){
            bus.$emit('product-'+event);
        },
        fetchLang(lang) {
            this.lang = lang;
        },
        fetchProducts() {
            let url = '/api/products';

            if (this.searchText) {
                url += '?q=' + encodeURIComponent(this.searchText.trim());
            }

            axios.get(url)
                .then(response => {
                    this.products = response.data;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        fetchCategories() {
            axios.get('/api/categories')
                .then(response => {
                    this.categories = response.data;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        editProduct(product) {
            this.formAction = this.labels[this.lang].update;
            this.formTitle = this.labels[this.lang].editProduct;
            this.product.id = product.id;
            this.product.name = product.name;
            this.product.category_id = product.category_id;
            this.product.created_at = product.created_at;
            this.product.updated_at = product.updated_at;
            this.modalOpen('payment');
        },
        deleteProduct(product) {
            if (confirm(this.labels[this.lang].textConfirDelete)) {
                axios.delete('/api/products/' + product.id)
                    .then(response => {
                        this.fetchProducts();
                        this.emitProducts('deleted');
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        },
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
</style>
