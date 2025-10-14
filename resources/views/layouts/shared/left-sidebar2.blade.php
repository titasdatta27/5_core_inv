<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu">

    <!-- Brand Logo Light -->
    <a href="{{ route('any', 'index') }}" class="logo logo-light">
        <span class="logo">
            <img src="/images/1920 x 557_Product Manager.png" alt="logo" style="width: 200px;
    height: auto;">
        </span>

    </a>

    <!-- Brand Logo Dark -->
    <a href="{{ route('any', 'index') }}" class="logo logo-dark">
        <span class="logo">
            <img src="/images/1920 x 557_Product Manager.png" alt="dark logo">
        </span>
   
    </a>

    <!-- Sidebar -left -->
    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <!--- Sidemenu -->
        <ul class="side-nav">

            <li class="side-nav-title">Main</li>

                            <li class="side-nav-item">
                    <a href="{{ route('any', 'index') }}" class="side-nav-link">
                        <i class="ri-dashboard-3-line"></i>
                        <span> Dashboard </span>
                    </a>
                </li>
            @endif

            {{-- User --}}
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarPages1" aria-expanded="false"
                        aria-controls="sidebarPages1" class="side-nav-link">
                        <i class="ri-user-line"></i>
                        <span>User</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarPages1">
                        <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('roles') }}">Roles</a>
                                </li>
                                <li>
                                    <a href="{{ route('permissions') }}">Permission</a>
                                </li>
                        </ul>
                    </div>
                </li>
            @endif


                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#suppliers" aria-expanded="false" aria-controls="suppliers"
                        class="side-nav-link">
                        <i class="ri-group-line"></i>
                        <span>Purchase Master</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="suppliers">
                        <ul class="side-nav-second-level">
                            <li>
                                <a href="{{ route('category.list') }}">Categories</a>
                            </li>
                            <li>
                                <a href="{{ route('supplier.list') }}">Suppliers</a>
                            </li>
                            <li>
                                <a href="{{ route('claim.reimbursement') }}">Claims & Reimbursements</a>
                            </li>
                            <li>
                                <a href="{{ route('forecast.analysis') }}">Forecast Analysis</a>
                            </li>
                            <li>
                                <a href="{{ route('to.order.analysis') }}">To Order Analysis</a>
                            </li>
                            <li>
                                <a href="{{ route('list-all-purchase-orders') }}">Purchase Contract</a>
                            </li>
                            <li>
                                <a href="{{ route('purchase.index') }}">Purchase</a>
                            </li>
                            <li>
                                <a href="{{ route('sourcing.index') }}">Sourcing</a>
                            </li>
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#ledger" aria-expanded="false"
                                    aria-controls="ledger">
                                    <span>Ledger</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="ledger">
                                    <ul class="side-nav-third-level">
                                        <li>
                                            <a href="{{ route('ledger.advance.payments') }}">Advance & Payments</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('supplier.ledger') }}">Supplier Ledger</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a href="{{ route('mfrg.in.progress') }}">MFRG In Progress</a>
                            </li>
                            <li>
                                <a href="{{ route('ready.to.ship') }}">Ready To Ship</a>
                            </li>
                            <li>
                                <a href="{{ route('china.load') }}">China Load</a>
                            </li>
                            <li>
                                <a href="{{ route('transit.container.details') }}">Transit Container INV</a>
                            </li>
                            <li>
                                <a href="{{ route('transit.container.changes') }}">Transit Container Changes</a>
                            </li>
                            <li>
                                <a href="{{ route('transit.container.new') }}">Transit Container New</a>
                            </li>
                            <li>
                                <a href="{{ route('container.planning') }}">Container Planning</a>
                            </li>
                            <li>
                                <a href="{{ route('on.sea.transit') }}">On Sea Transit</a>
                            </li>
                            <li>
                                <a href="{{ route('on.road.transit') }}">On Road Transit</a>
                            </li>
                            <li>
                                <a href="{{ route('quality.enhance') }}">Quality Enhance</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif


                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarPagess" aria-expanded="false"
                        aria-controls="sidebarPagess" class="side-nav-link">
                        <i class="ri-pages-line"></i>
                        <span>Product Masters</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarPagess">
                        <ul class="side-nav-second-level">
                            <li>
                                <a href="{{ route('product.master') }}">CP Masters</a>
                            </li>
                            <li>
                                <a href="{{ url('pricing-master.pricing_master') }}">Pricing masters</a>
                            </li>
                            <li>
                                <a href="{{ url('pricing-masters.pricing_masters') }}">Pricing masters </a>
                            </li>
                            <li>
                                <a href="https://listing-analysis.5coremanagement.com/public/login" target="_blank"
                                    rel="noopener noreferrer">Listing Analysis</a>
                            </li>
                            <li>
                                <a href="{{ route('costprice.analysis') }}">Cost Price Analysis</a>
                            </li>
                            <li>
                                <a href="{{ route('movement.analysis') }}">Movement Analysis</a>
                            </li>
                            <li>
                                <a href="{{ route('tobedc.list') }}">2BDC</a>
                            </li>
                            <li>
                                <a href="{{ route('second', ['pages', 'transit-analysis']) }}">Transit Analysis</a>
                            </li>
                            <li>
                                <a href="{{ route('review.analysis') }}">Review Analysis</a>
                            </li>
                            <li>
                                <a href="{{ route('pRoi.analysis') }}">Profit & ROI Analysis</a>
                            </li>
                            <li>
                                <a href="{{ route('return.analysis') }}">Returns Analysis</a>
                            </li>
                            <li>
                                <a href="{{ route('stock.analysis') }}">Stock Verification</a>
                            </li>
                            <li>
                                <a href="{{ route('shortfall.analysis') }}">Shortfall Analysis</a>
                            </li>
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarAdvMaster" aria-expanded="false"
                                    aria-controls="sidebarAdvMaster" class="side-nav-link collapsed">
                                    <span class="menu-arrow"></span>
                                    <span>Advertisement Master</span>
                                </a>
                                <div class="collapse" id="sidebarAdvMaster">
                                    <ul class="side-nav-second-level">
                                        <!-- Product Wise Section -->
                                        <li class="side-nav-item">
                                            <a data-bs-toggle="collapse" href="#productWise" aria-expanded="false"
                                                aria-controls="productWise" class="collapsed">
                                                <span>Product Wise</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="productWise">
                                                <ul class="side-nav-third-level">
                                                    <!-- PPC Section -->
                                                    <li class="side-nav-item">
                                                        <a data-bs-toggle="collapse" href="#ppcProduct"
                                                            aria-expanded="false" aria-controls="ppcProduct"
                                                            class="collapsed">
                                                            <span>PPC</span>
                                                            <span class="menu-arrow"></span>
                                                        </a>
                                                        <div class="collapse" id="ppcProduct">
                                                            <ul class="side-nav-fourth-level">
                                                                <li>
                                                                    <a data-bs-toggle="collapse" href="#ppcProduct1"
                                                                        aria-expanded="false"
                                                                        aria-controls="ppcProduct1" class="collapsed">
                                                                        <span>KW Advt</span>
                                                                        <span class="menu-arrow"></span>
                                                                    </a>
                                                                    <div class="collapse" id="ppcProduct1">
                                                                        <ul class="side-nav-fifth-level">
                                                                            <li><a href="{{ route('advertisment.kw.amazon') }}">Amazon</a></li>
                                                                            <li><a href="{{ route('advertisment.kw.eBay') }}">eBay</a></li>
                                                                            <li><a href="{{ route('advertisment.kw.walmart') }}">Walmart</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </li>
                                                                <li>
                                                                    <a data-bs-toggle="collapse" href="#ppcProduct2"
                                                                        aria-expanded="false"
                                                                        aria-controls="ppcProduct2" class="collapsed">
                                                                        <span>Prod Target Advt</span>
                                                                        <span class="menu-arrow"></span>
                                                                    </a>
                                                                    <div class="collapse" id="ppcProduct2">
                                                                        <ul class="side-nav-fifth-level">
                                                                            <li><a href="{{ route('advertisment.prod.target.Amazon') }}">Amazon</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </li>
                                                                <li>
                                                                    <a data-bs-toggle="collapse" href="#ppcProduct3"
                                                                        aria-expanded="false"
                                                                        aria-controls="ppcProduct3" class="collapsed">
                                                                        <span>Headline Advt</span>
                                                                        <span class="menu-arrow"></span>
                                                                    </a>
                                                                    <div class="collapse" id="ppcProduct3">
                                                                        <ul class="side-nav-fifth-level">
                                                                            <li><a href="{{ route('advertisment.headline.Amazon') }}">Amazon</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </li>
                                                                <li>
                                                                    <a data-bs-toggle="collapse" href="#ppcProduct4"
                                                                        aria-expanded="false"
                                                                        aria-controls="ppcProduct4" class="collapsed">
                                                                        <span>Promoted Advt</span>
                                                                        <span class="menu-arrow"></span>
                                                                    </a>
                                                                    <div class="collapse" id="ppcProduct4">
                                                                        <ul class="side-nav-fifth-level">
                                                                            <li><a href="{{ route('advertisment.promoted.eBay') }}">eBay</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </li>
                                                                <li>
                                                                    <a data-bs-toggle="collapse" href="#ppcProduct5"
                                                                        aria-expanded="false"
                                                                        aria-controls="ppcProduct5" class="collapsed">
                                                                        <span>Shopping Advt</span>
                                                                        <span class="menu-arrow"></span>
                                                                    </a>
                                                                    <div class="collapse" id="ppcProduct5">
                                                                        <ul class="side-nav-fifth-level">
                                                                            <li><a href="{{ route('advertisment.shopping.google') }}">Google Shopping</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </li>
                                                                <li>
                                                                    <a data-bs-toggle="collapse" href="#ppcProduct6"
                                                                        aria-expanded="false"
                                                                        aria-controls="ppcProduct6" class="collapsed">
                                                                        <span>Demand Gen Parents</span>
                                                                        <span class="menu-arrow"></span>
                                                                    </a>
                                                                    <div class="collapse" id="ppcProduct6">
                                                                        <ul class="side-nav-fifth-level">
                                                                            <li><a href="{{ route('advertisment.demand.gen.googleNetworks') }}">Google Networks</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </li>

                                                    <!-- Budget Control Section -->
                                                    <li class="side-nav-item">
                                                        <a data-bs-toggle="collapse" href="#budgetControlProduct"
                                                            aria-expanded="false" aria-controls="budgetControlProduct"
                                                            class="collapsed">
                                                            <span>Budget Control</span>
                                                            <span class="menu-arrow"></span>
                                                        </a>
                                                        <div class="collapse" id="budgetControlProduct">
                                                            <ul class="side-nav-fourth-level">
                                                                <li>
                                                                    <a data-bs-toggle="collapse" href="#budgetControlProduct1"
                                                                        aria-expanded="false"
                                                                        aria-controls="budgetControlProduct1"
                                                                        class="collapsed">
                                                                        <span>Meta Parent Advt Images</span>
                                                                        <span class="menu-arrow"></span>
                                                                    </a>
                                                                    <div class="collapse" id="budgetControlProduct1">
                                                                        <ul class="side-nav-fifth-level">
                                                                            <li><a href="{{ route('advertisment.demand.productWise.metaParent.img.facebook') }}">FB</a></li>
                                                                            <li><a href="{{ route('advertisment.demand.productWise.metaParent.img.instagram') }}">Insta</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </li>
                                                                <li>
                                                                    <a data-bs-toggle="collapse" href="#budgetControlProduct2"
                                                                        aria-expanded="false"
                                                                        aria-controls="budgetControlProduct2"
                                                                        class="collapsed">
                                                                        <span>Meta Parent Advt Video</span>
                                                                        <span class="menu-arrow"></span>
                                                                    </a>
                                                                    <div class="collapse" id="budgetControlProduct2">
                                                                        <ul class="side-nav-fifth-level">
                                                                            <li><a href="{{ route('advertisment.demand.productWise.metaParent.video.facebook') }}">FB</a></li>
                                                                            <li><a href="{{ route('advertisment.demand.productWise.metaParent.video.instagram') }}">Insta</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>

                                        <!-- Group Wise Section -->
                                        <li class="side-nav-item">
                                            <a data-bs-toggle="collapse" href="#groupWise" aria-expanded="false"
                                                aria-controls="groupWise" class="collapsed">
                                                <span>Group Wise</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="groupWise">
                                                <ul class="side-nav-third-level">
                                                    <!-- PPC Section -->
                                                    <li class="side-nav-item">
                                                        <a data-bs-toggle="collapse" href="#ppcGroup"
                                                            aria-expanded="false" aria-controls="ppcGroup"
                                                            class="collapsed">
                                                            <span>PPC</span>
                                                            <span class="menu-arrow"></span>
                                                        </a>
                                                        <div class="collapse" id="ppcGroup">
                                                            <ul class="side-nav-fourth-level">
                                                                <li>
                                                                    <a data-bs-toggle="collapse" href="#ppcGroup1"
                                                                        aria-expanded="false"
                                                                        aria-controls="ppcGroup1" class="collapsed">
                                                                        <span>Serp Advt</span>
                                                                        <span class="menu-arrow"></span>
                                                                    </a>
                                                                    <div class="collapse" id="ppcGroup1">
                                                                        <ul class="side-nav-fifth-level">
                                                                            <li><a href="#">Google SERP</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </li>
                                                                <li>
                                                                    <a data-bs-toggle="collapse" href="#ppcGroup2"
                                                                        aria-expanded="false"
                                                                        aria-controls="ppcGroup2" class="collapsed">
                                                                        <span>Youtube Advt Groups</span>
                                                                        <span class="menu-arrow"></span>
                                                                    </a>
                                                                    <div class="collapse" id="ppcGroup2">
                                                                        <ul class="side-nav-fifth-level">
                                                                            <li><a href="#">Youtube</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </li>
                                                                <li>
                                                                    <a data-bs-toggle="collapse" href="#ppcGroup3"
                                                                        aria-expanded="false"
                                                                        aria-controls="ppcGroup3" class="collapsed">
                                                                        <span>Demand Gen Groups</span>
                                                                        <span class="menu-arrow"></span>
                                                                    </a>
                                                                    <div class="collapse" id="ppcGroup3">
                                                                        <ul class="side-nav-fifth-level">
                                                                            <li><a href="#">Google Networks</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </li>

                                                    <!-- Budget Control Section -->
                                                    <li class="side-nav-item">
                                                        <a data-bs-toggle="collapse" href="#budgetControlGroup"
                                                            aria-expanded="false" aria-controls="budgetControlGroup"
                                                            class="collapsed">
                                                            <span>Budget Control</span>
                                                            <span class="menu-arrow"></span>
                                                        </a>
                                                        <div class="collapse" id="budgetControlGroup">
                                                            <ul class="side-nav-fourth-level">
                                                                <li>
                                                                    <a data-bs-toggle="collapse" href="#budgetControlGroup1"
                                                                        aria-expanded="false"
                                                                        aria-controls="budgetControlGroup1"
                                                                        class="collapsed">
                                                                        <span>Meta Parent Advt Images</span>
                                                                        <span class="menu-arrow"></span>
                                                                    </a>
                                                                    <div class="collapse" id="budgetControlGroup1">
                                                                        <ul class="side-nav-fifth-level">
                                                                            <li><a href="#">FB</a></li>
                                                                            <li><a href="#">Insta</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </li>
                                                                <li>
                                                                    <a data-bs-toggle="collapse" href="#budgetControlGroup2"
                                                                        aria-expanded="false"
                                                                        aria-controls="budgetControlGroup2"
                                                                        class="collapsed">
                                                                        <span>Meta Parent Advt Video</span>
                                                                        <span class="menu-arrow"></span>
                                                                    </a>
                                                                    <div class="collapse" id="budgetControlGroup2">
                                                                        <ul class="side-nav-fifth-level">
                                                                            <li><a href="#">FB</a></li>
                                                                            <li><a href="#">Insta</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif


                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#channelSidebarPages" aria-expanded="false"
                        aria-controls="sidebarPages" class="side-nav-link">
                        <i class="ri-wallet-2-line"></i>
                        <span>Channel</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="channelSidebarPages">
                        <ul class="side-nav-second-level">
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#activeChannelMasyer" aria-expanded="false"
                                    aria-controls="activeChannelMasyer">
                                    <span>Channel Master</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="activeChannelMasyer">
                                    <ul class="side-nav-third-level">
                                        <li>
                                            <a href="{{ route('channel.master', ['channels', 'channel-masters']) }}"
                                                target="_blank">Active Channels</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('opportunity.index') }}">Opportunities</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('application.approvals.index') }}">Application &
                                                Approvals</a>
                                        </li>
                                        <li>
                                            <a href=" {{ route('setup.account.index') }}">Setup Account & Shop</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a href="{{ route('master.pricing.inc.dsc') }}" target="_blank">Price Adjustment
                                    Manager</a>
                            </li>

                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#movementAnalysisMenu" aria-expanded="false"
                                    aria-controls="movementAnalysisMenu">
                                    <span>Movement Analysis</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="movementAnalysisMenu">
                                    <ul class="side-nav-third-level">
                                        <li>
                                            <a href="{{ route('channel.movement.analysis') }}" target="_blank">Sales
                                                and Analysis</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="{{ route('new.marketplaces.dashboard') }}">New Marketplaces</a>
                            </li>

                            <li>
                                <a href="{{ route('promotion.master') }}">Promotion Master</a>
                            </li>

                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#account-health-master" aria-expanded="false"
                                    aria-controls="account-health-master" class="side-nav-link">
                                    <span>Account Health Master</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="account-health-master">
                                    <ul class="side-nav-second-level">
                                        <li>
                                            <a href="{{ route('odr.rate') }}">ODR Rate</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('fullfillment.rate') }}">Fulfillment Rate</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('valid.tracking.rate') }}">Valid Tracking Rate</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('late.shipment.rate') }}">Late Shipment</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('on.time.delivery.rate') }}">On Time Delivery</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('negative.seller.rate') }}">Negative Seller</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('a_z.claims.rate') }}">A-Z Claims</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('voilation.rate') }}">Voilation/Compliance</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('refund.rate') }}">Refunds / Returns</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#reviewMaster" aria-expanded="false"
                                    aria-controls="reviewMaster">
                                    <span> Review Master </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="reviewMaster">
                                    <ul class="side-nav-third-level">
                                        <li>
                                            <a href="{{ route('review.master.dashboard') }}">Review Dashboard</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('review.masters.amazon') }}">Amazon Product Reviews</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a href="{{ route('return.master') }}">Return Analysis</a>
                            </li>
                            <li>
                                <a href="{{ route('shipping.master.list') }}">Shipping Master</a>
                            </li>
                            <li>
                                <a href="{{ route('traffic.master.list') }}">Traffic Master</a>
                            </li>
                            <li>
                                <a href="{{ route('expenses.master') }}">Expenses Analysis</a>
                            </li>
                            <li>
                                <a href="{{ route('review.master') }}">Review Analysis</a>
                            </li>
                            <li>
                                <a href="{{ route('health.master') }}">Health Analysis</a>
                            </li>
                            <li>
                                <a href="{{ route('channel.master', ['channels', 'returns-analysis']) }}">Listing
                                    analysis</a>
                            </li>
                            <li>
                                <a href="{{ route('channel.master', ['channels', 'expenses-analysis']) }}">Shipping
                                    analysis</a>
                            </li>
                            <li>
                                <a href="{{ route('channel.master', ['channels', 'advertisement-analysis']) }}">C Care
                                    Analysis</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPages2" aria-expanded="false"
                    aria-controls="sidebarPages2" class="side-nav-link">
                    <i class="ri-store-3-line"></i>
                    <span>Marketing Masters</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarPages2">
                    <ul class="side-nav-second-level">
                            <li>
                                <a href="{{ route('listingMaster') }}">Listing Masters</a>
                            </li>

                        <li>
                            <a href="{{ url('listing-master-counts') }}">Low Visibility Masters</a>
                        </li>
                        <li>
                            <a href="{{ route('listing.audit') }}">Listing Audit Masters</a>
                        </li>
                        <li>
                            <a href="{{ route('zero.visibility') }}">0 Views Masters</a>
                        </li>
                        <li>
                            <a href="{{ route('carousel.sales') }}">Carousel Sales Master</a>
                        </li>
                        <li>
                            <a href="{{ route('email.marketing') }}">Email Marketing</a>
                        </li>
                        <li>
                            <a href="{{ route('whatsapp.marketing') }}">Whatsapp Marketing</a>
                        </li>
                        <li>
                            <a href="{{ route('sms.marketing') }}">SMS Marketing</a>
                        </li>
                        <li>
                            <a href="{{ route('dm.marketing') }}">DM Marketing</a>
                        </li>
                        <li>
                            <a href="{{ route('phone.marketing') }}">Phone Appt Marketing</a>
                        </li>
                        <li>
                            <a href="{{ route('letter.marketing') }}">Letter Marketing</a>
                        </li>

                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarvideoSales" aria-expanded="false"
                                    aria-controls="sidebarvideoSales">
                                    <span>Video Directory</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarvideoSales">
                                    <ul class="side-nav-third-level">
                                        <li class="side-nav-item">
                                            <a data-bs-toggle="collapse" href="#videoSalesSubmenu"
                                                aria-expanded="false" aria-controls="videoSalesSubmenu">
                                                <span>Product Group</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="videoSalesSubmenu">
                                                <ul class="side-nav-third-level">
                                                    <li>
                                                        <a href="{{ route('mm.video.posted') }}">Product Videos</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('mm.product.video.upload') }}">Product Video
                                                            Upload</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="side-nav-item">
                                            <a data-bs-toggle="collapse" href="#videoSalesSubmenu2"
                                                aria-expanded="false" aria-controls="videoSalesSubmenu2">
                                                <span>Assembly Group</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="videoSalesSubmenu2">
                                                <ul class="side-nav-third-level">
                                                    <li>
                                                        <a href="{{ route('mm.assembly.video.posted') }}">Assembly
                                                            Video</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('mm.assembly.video.upload') }}">Assembly
                                                            Video Upload</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="side-nav-item">
                                            <a data-bs-toggle="collapse" href="#videoSalesSubmenu3"
                                                aria-expanded="false" aria-controls="videoSalesSubmenu3">
                                                <span>3D Video Group</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="videoSalesSubmenu3">
                                                <ul class="side-nav-third-level">
                                                    <li>
                                                        <a href="{{ route('mm.3d.video.posted') }}">3D Video</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('mm.3d.video.upload') }}">3D Video
                                                            Upload</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="side-nav-item">
                                            <a data-bs-toggle="collapse" href="#videoSalesSubmenu4"
                                                aria-expanded="false" aria-controls="videoSalesSubmenu4">
                                                <span>360 Video Group</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="videoSalesSubmenu4">
                                                <ul class="side-nav-third-level">
                                                    <li>
                                                        <a href="{{ route('mm.360.video.posted') }}">360 Video</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('mm.360.video.upload') }}">360 Video
                                                            Upload</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="side-nav-item">
                                            <a data-bs-toggle="collapse" href="#videoSalesSubmenu5"
                                                aria-expanded="false" aria-controls="videoSalesSubmenu5">
                                                <span>Benefits Video Group</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="videoSalesSubmenu5">
                                                <ul class="side-nav-third-level">
                                                    <li>
                                                        <a href="{{ route('mm.benefits.video.posted') }}">Benefits
                                                            Video</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('mm.benefits.video.upload') }}">Benefits
                                                            Video Upload</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="side-nav-item">
                                            <a data-bs-toggle="collapse" href="#videoSalesSubmenu6"
                                                aria-expanded="false" aria-controls="videoSalesSubmenu6">
                                                <span>DIY Video Group</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="videoSalesSubmenu6">
                                                <ul class="side-nav-third-level">
                                                    <li>
                                                        <a href="{{ route('mm.diy.video.posted') }}">DIY Video</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('mm.diy.video.upload') }}">DIY Video
                                                            Upload</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="side-nav-item">
                                            <a data-bs-toggle="collapse" href="#shoppable" aria-expanded="false"
                                                aria-controls="shoppable">
                                                <span>Shoppable Video Group</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="shoppable">
                                                <ul class="side-nav-third-level">
                                                    <li>
                                                        <a href="{{ route('one.ration') }}">1:1 RATIO</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('four.ration') }}">4:5 RATIO</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('nine.ration') }}">9:16 RATIO</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('sixteen.ration') }}">16:9 RATIO</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                        <li class="side-nav-item">
                            <a data-bs-toggle="collapse" href="#video-ads-master" aria-expanded="false"
                                aria-controls="video-ads-master" class="side-nav-link">
                                <span>Video Ads Master</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="video-ads-master">
                                <ul class="side-nav-second-level">
                                    <li>
                                        <a href="{{ route('facebook.ads.master') }}">Facebook Ads</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('facebook.feed.ads.master') }}">Facebook In Feed</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('facebook.reel.ads.master') }}">Facebook Reel Ads</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('instagram.ads.master') }}">Instagram Ads</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('instagram.feed.ads.master') }}">Instagram In Feed</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('instagram.reel.ads.master') }}">Instagram Reel Ads</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('youtube.ads.master') }}">YouTube Ads</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('youtube.shorts.ads.master') }}">YouTube Shorts Ads</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('tiktok.ads.master') }}">Tik Tok Video Ads</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                          <li class="side-nav-item">
                            <a data-bs-toggle="collapse" href="#facebook-ads-master" aria-expanded="false"
                                aria-controls="facebook-ads-master" class="side-nav-link">
                                <span>Facebook Ads Manager</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="facebook-ads-master">
                                <ul class="side-nav-second-level">
                                    <li>
                                        <a href="{{ route('facebook.ads.index') }}">Image Carousel Ad Running</a>
                                    </li>
                                    <li>
                                        <a href="#">Image Carousel Ad Running</a>
                                    </li>
                                    <li>
                                        <a href="#">Image Carousel Ad Running</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                    
                            <li>
                                <a data-bs-toggle="collapse" href="#lqsSubmenu" aria-expanded="false"
                                    aria-controls="lqsSubmenu">
                                    <span>LQS Masters</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="lqsSubmenu">
                                    <ul class="side-nav-fourth-level">
                                            <li>
                                                <a href="{{ route('listingLQS.master') }}">LQS - Listing</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('cvrLQS.master') }}">Amazon LQS - CVR</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('ebaycvrLQS.master') }}">Ebay LQS - CVR</a>
                                            </li>
                                    </ul>
                                </div>
                            </li>
                  

                        <li>
                            <a href="#">Traffic And Session Masters</a>
                        </li>
                        <li>
                            <a href="#">Conversion Content Masters</a>
                        </li>
                        <li>
                            <a href="#">Conversion Other Masters</a>
                        </li>
                        <li>
                            <a href="#">Pricing Masters</a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Shopify --}}
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPages" aria-expanded="false"
                    aria-controls="sidebarPages" class="side-nav-link">
                    <i class="ri-shopping-bag-line"></i>
                    <span>Shopify</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarPages">
                    <ul class="side-nav-second-level">
                        <li>
                            <a href="{{ route('shopify', ['products', 'shopify-Products']) }}">Products</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['products', 'inventory']) }}">Inventory</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['products', 'amazon-Products']) }}">Movement Analysis Y2Y</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['products', 'amazon-Products']) }}">Movement Anlys M2M</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['products', 'amazon-Products']) }}">Movement Anlys S2S</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['products', 'amazon-Products']) }}">Forecast Analysis</a>
                        </li>
                    </ul>
                </div>
            </li>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarPages" aria-expanded="false"
                        aria-controls="sidebarPages" class="side-nav-link">
                        <i class="ri-shopping-bag-line"></i>
                        <span>Shopify</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarPages">
                        <ul class="side-nav-second-level">
                            <li>
                                <a href="{{ route('shopify', ['products', 'shopify-Products']) }}">Products</a>
                            </li>
                            <li>
                                <a href="{{ route('second', ['products', 'inventory']) }}">Inventory</a>
                            </li>
                            <li>
                                <a href="{{ route('second', ['products', 'amazon-Products']) }}">Movement Analysis Y2Y</a>
                            </li>
                            <li>
                                <a href="{{ route('second', ['products', 'amazon-Products']) }}">Movement Anlys M2M</a>
                            </li>
                            <li>
                                <a href="{{ route('second', ['products', 'amazon-Products']) }}">Movement Anlys S2S</a>
                            </li>
                            <li>
                                <a href="{{ route('second', ['products', 'amazon-Products']) }}">Forecast Analysis</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            {{-- Inventory Management --}}

                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#invsidebarPages" aria-expanded="false"
                        aria-controls="sidebarPages" class="side-nav-link">
                        <i class="ri-archive-drawer-line"></i>
                        <span>Inventory Management</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="invsidebarPages">
                        <ul class="side-nav-second-level">
                            <li>
                                <a href="{{ route('view-inventory') }}">View Inventory</a>
                            </li>
                            <li>
                                <a href="{{ route('verify-adjust') }}">Verifications & Adjustments</a>
                            </li>
                            <li>
                                <a href="{{ route('incoming.view') }}">Incoming</a>
                            </li>
                            <li>
                                <a href="{{ route('outgoing.view') }}">Outgoing</a>
                            </li>
                            <li>
                                <a href="{{ route('stock.adjustment.view') }}">Stock Adjustment</a>
                            </li>
                            <li>
                                <a href="{{ route('stock.transfer.view') }}">Stock Transfer</a>
                            </li>
                            <li>
                                <a href="{{ route('stock.balance.view') }}">Stock Balance</a>
                            </li>
                            <li>
                                <a href="#">Trash Entries</a>
                            </li>
                            <li>
                                <a href="#">Pallete Sales</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            {{-- Inventory Warehouse --}}

                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#waresidebarPages" aria-expanded="false"
                        aria-controls="sidebarPages" class="side-nav-link">
                        <i class="ri-building-4-line"></i>
                        <span>Inventory Warehouse</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="waresidebarPages">
                        <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('list_all_warehouses') }}">List All Warehouses</a>
                                </li>


                                <li>
                                    <a href="#">Inventory Locator</a>
                                </li>
                                <li>
                                    <a href="#">Transfers</a>
                                </li>

                            <li>
                                <a href="{{ route('returns.godown') }}">Returns Godown</a>
                            </li>
                            <li>
                                <a href="{{ route('openbox.godown') }}">Open Box Godown</a>
                            </li>
                            <li>
                                <a href="{{ route('showroom.godown') }}">Showroom Godown</a>
                            </li>
                            <li>
                                <a href="{{ route('useditem.godown') }}">Used Item Godown</a>
                            </li>
                            <li>
                                <a href="{{ route('trash.godown') }} ">Trash Godown</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            {{-- Marketplace --}}
           

            @if ($hasMarketplace)
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarLayouts" aria-expanded="false"
                        aria-controls="sidebarLayouts" class="side-nav-link">
                        <i class="ri-store-line"></i> <!-- Marketplace icon -->
                        <span class="menu-arrow"></span>
                        <span>Marketplace </span>
                    </a>
                    <div class="collapse" id="sidebarLayouts">
                        <ul class="side-nav-second-level">

                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarSecondLevel" aria-expanded="false"
                                    aria-controls="sidebarSecondLevel">
                                    <span> Amazon </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarSecondLevel">
                                    <ul class="side-nav-third-level">
                                        
                                            <li>
                                                <a data-bs-toggle="collapse" href="#amazonSubmenu"
                                                    aria-expanded="false" aria-controls="amazonSubmenu">
                                                    <span>Amazon View</span>
                                                    <span class="menu-arrow"></span>
                                                </a>
                                                <div class="collapse" id="amazonSubmenu">
                                                    <ul class="side-nav-fourth-level">
                                                            <li>
                                                                <a href="{{ route('overall.amazon') }}">Amazon
                                                                    Analytics</a>
                                                            </li>
                                                        
                                                            <li>
                                                                <a href="{{ route('amazon.zero.view') }}">Amazon 0
                                                                    view</a>
                                                            </li>
                                                      
                                                            <li>
                                                                <a href="{{ route('amazon.low.visibility.view') }}">
                                                                    Low Visibility</a>
                                                            </li>
                                                        <li>
                                                            <a href="{{ route('amazon.low.visibility.view.fba') }}">
                                                                Low Visibility FBA</a>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('amazon.low.visibility.view.fbm') }}">
                                                                Low Visibility FBM</a>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('amazon.low.visibility.view.both') }}">
                                                                Low Visibility BOTH</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </li>
                                       

                                        {{-- Amazon FBA submenu --}}
                                        
                                                <a data-bs-toggle="collapse" href="#amazonFbaSubmenu"
                                                    aria-expanded="false">
                                                    <span>Amazon FBA</span>
                                                    <span class="menu-arrow"></span>
                                                </a>
                                                <div class="collapse" id="amazonFbaSubmenu">
                                                    <ul class="side-nav-fourth-level">
                                                            <li>
                                                                <a href="{{ route('overall.amazon.fba') }}">Amazon
                                                                    FBA
                                                                    Analysis</a>
                                                            </li>
                                                            <li>
                                                                <a href="{{ route('overall.amazon.fbainv') }}">FBA
                                                                    INV
                                                                    AGE</a>
                                                            </li>
                                                    </ul>
                                                </div>
                                            </li>

                                            <li>
                                                <a href="#">Amazon Pricing</a>
                                            </li>

                                            <li>
                                                <a href="{{ route('listing.amazon') }}">Listing Amazon</a>
                                            </li>

                                            <li>
                                                <a href="{{ route('listing.audit.amazon') }}">Listing Audit
                                                    Amazon</a>
                                            </li>

                                        <li>
                                            <a href="{{ route('amazon.pricing.cvr') }}">Amazon Pricing - CVR</a>
                                        </li>

                                        <li>

                                            <a href="{{ route('amazon.pricing.increase') }}">Amz Price Decrease
                                                CVR</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('amazon.pricing.inc') }}">Amz Price Increase CVR</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('amazon.ad-running.list') }}">Amazon Ad Running</a>
                                        </li>
                                        <li>
                                            <a data-bs-toggle="collapse" href="#amazonACOS" aria-expanded="false"
                                                aria-controls="amazonACOS">
                                                <span>Amazon ACOS Control</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="amazonACOS">
                                                <ul class="side-nav-fourth-level">
                                                    <li>
                                                        <a href="{{ route('amazon.acos.kw.control') }}">Amazon ACOS KW</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('amazon.acos.hl.control') }}">Amazon ACOS HL</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('amazon.acos.pt.control') }}">Amazon ACOS PT</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li>
                                            <a data-bs-toggle="collapse" href="#amazonBudget" aria-expanded="false"
                                                aria-controls="amazonBudget">
                                                <span>Amazon Budget</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="amazonBudget">
                                                <ul class="side-nav-fourth-level">
                                                    <li>
                                                        <a href="{{ url('campaign') }}">Utilization</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('campaign.under') }} ">Under utilised</a>
                                                    </li>
                                                    <li>
                                                        <a href=" {{ route('campaign.over') }} ">Over utilised</a>
                                                    </li>
                                                    <li>
                                                        <a href=" {{ route('amazon-sp.amz-utilized-bgt-kw') }} ">AMZ > UTILIZED BGT KW</a>
                                                    </li>
                                                    <li>
                                                        <a href=" {{ route('amazon-sb.amz-utilized-bgt-hl') }} ">AMZ > UTILIZED BGT HL</a>
                                                    </li>
                                                    <li>
                                                        <a href=" {{ route('amazon-sp.amz-utilized-bgt-pt') }} ">AMZ > UTILIZED BGT PT</a>
                                                    </li>
                                                    <li>
                                                        <a href=" {{ route('amazon-sp.amz-under-utilized-bgt-kw') }} ">AMZ < UTILIZED BGT KW</a>
                                                    </li>
                                                    <li>
                                                        <a href=" {{ route('amazon-sb.amz-under-utilized-bgt-hl') }} ">AMZ < UTILIZED BGT HL</a>
                                                    </li>
                                                    <li>
                                                        <a href=" {{ route('amazon-sp.amz-under-utilized-bgt-pt') }} ">AMZ < UTILIZED BGT PT</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('amazon.amz-correctly-utilized-bgt-kw') }}">CORRECTLY UTILIZED KW</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('amazon.amz-correctly-utilized-bgt-hl') }}">CORRECTLY UTILIZED HL</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('amazon.amz-correctly-utilized-bgt-pt') }}">CORRECTLY UTILIZED PT</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        {{-- Add EXtra For Amazon Pricing --}}
                                    </ul>
                                </div>
                            </li>
                            {{-- eBay --}}
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarThirdLevel" aria-expanded="false"
                                    aria-controls="sidebarThirdLevel" class="side-nav-link">
                                    <span> eBay </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarThirdLevel">
                                    <ul class="side-nav-third-level">
                                        <li>
                                            <a data-bs-toggle="collapse" href="#ebaySubmenu"
                                                aria-expanded="false" aria-controls="ebaySubmenu">
                                                <span>eBay View</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="ebaySubmenu">
                                                <ul class="side-nav-fourth-level">
                                                    <li>
                                                        <a href="{{ route('ebay') }}">eBay Analytics</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('ebay.zero.view') }}">eBay 0 view</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('ebay.low.visibility.view') }}">eBay Low Visibility</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>

                                        <li>
                                            <a href="{{ route('listing.ebay') }}">Listing eBay</a>
                                        </li>

                                        <li>
                                            <a href="{{ route('listing.audit.ebay') }}">Listing Audit eBay</a>
                                        </li>

                                        <li>
                                            <a href="{{ url('ebay-pricing-cvr') }}">Ebay Pricing - CVR</a>
                                        </li>

                                        <li>
                                            <a href="{{ url('ebay-pricing-decrease') }}">Ebay Pricing Decrease</a>
                                        </li>

                                        <li>
                                            <a href="{{ url('ebay-pricing-increase') }}">Ebay Pricing Increase</a>
                                        </li>

                                        <li>
                                            <a href="{{ route('ebay.acos.index') }}">Ebay ACOS Control</a>
                                        </li>

                                        <li>
                                            <a href="{{ route('ebay-over-uti-acos-pink') }}">EBAY > UTI ACOS PINK</a>
                                        </li>

                                        <li>
                                            <a href="{{ route('ebay-over-uti-acos-green') }}">EBAY > UTI ACOS GREEN</a>
                                        </li>

                                        <li>
                                            <a href="{{ route('ebay-over-uti-acos-red') }}">EBAY > UTI ACOS RED</a>
                                        </li>

                                        <li>
                                            <a href="{{ route('ebay-under-uti-acos-pink') }}">EBAY < UTI ACOS PINK</a>
                                        </li>

                                        <li>
                                            <a href="{{ route('ebay-under-uti-acos-green') }}">EBAY < UTI ACOS GREEN</a>
                                        </li>

                                        <li>
                                            <a href="{{ route('ebay-under-uti-acos-red') }}">EBAY < UTI ACOS RED</a>
                                        </li>

                                    </ul>
                                </div>
                            </li>

                            {{-- Shopify --}}
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarshopifyb2c" aria-expanded="false"
                                    aria-controls="sidebarshopifyb2c">
                                    <span> Shopify B2C </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarshopifyb2c">
                                    <ul class="side-nav-third-level">
                                        <li>
                                            <a data-bs-toggle="collapse" href="#shopifyb2cSubmenu"
                                                aria-expanded="false" aria-controls="shopifyb2cSubmenu">
                                                <span>Shopify B2C View</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="shopifyb2cSubmenu">
                                                <ul class="side-nav-fourth-level">
                                                    <li>
                                                        <a href="{{ route('shopifyB2C') }}">Shopify B2C Analytics</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('shopifyB2C.zero.view') }}">Shopify B2C 0 view</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('shopifyB2C.low.visibility.view') }}">ShopifyB2c Low Visibility</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>

                                        <li>
                                            <a href="{{ route('listing.shopifyb2c') }}">Listing Shopify B2C</a>
                                        </li>

                                        <li>
                                            <a href="{{ route('listing.audit.shopifyb2c') }}">Listing Audit Shopify B2C</a>
                                        </li>

                                        <li>
                                            <a href="{{ url('shopify-pricing-cvr') }}">Shopify Pricing - CVR</a>
                                        </li>

                                        <li>
                                            <a href="{{ url('shopify-pricing-increase-decrease') }}">Pricing - Increase/Decrease</a>
                                        </li>

                                    </ul>
                                </div>
                            </li>

                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarmacy" aria-expanded="false"
                                    aria-controls="sidebarmacy">
                                    <span> Macy's </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarmacy">
                                    <ul class="side-nav-third-level">
                                        <li>
                                            <a data-bs-toggle="collapse" href="#macysSubmenu"
                                                aria-expanded="false" aria-controls="macysSubmenu">
                                                <span>Macy's View</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="macysSubmenu">
                                                <ul class="side-nav-fourth-level">
                                                    <li>
                                                        <a href="{{ route('macys') }}">Macy's Analytics</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('macy.zero.view') }}">Macy's 0 view</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('macy.low.visibility.view') }}">Macy's Low Visibility</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>

                                        <li>
                                            <a href="{{ route('listing.macys') }}">Listing Macy's</a>
                                        </li>

                                        <li>
                                            <a href="{{ route('listing.audit.macy') }}">Listing Audit Macy's</a>
                                        </li>

                                        <li>
                                            <a href="{{ url('macy-pricing-cvr') }}">Macy's Pricing CVR</a>
                                        </li>

                                        <li>
                                            <a href="{{ url('macy-pricing-increase-decrease') }}">Pricing - Increase / Decrease</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <!-- <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarneweggb2c" aria-expanded="false"
                                    aria-controls="sidebarneweggb2c">
                                    <span> Newegg B2C </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarneweggb2c">
                                    <ul class="side-nav-third-level">
                                        @if (
                                            (isset($permissions['newegg_b2c']) && in_array('view', $permissions['newegg_b2c'])) ||
                                                (isset($permissions['neweggb2c_zero_view']) && in_array('view', $permissions['neweggb2c_zero_view'])) ||
                                                (isset($permissions['neweggb2c_low_visibility']) && in_array('view', $permissions['neweggb2c_low_visibility'])))
                                            <li>
                                                <a data-bs-toggle="collapse" href="#neweggb2cSubmenu"
                                                    aria-expanded="false" aria-controls="neweggb2cSubmenu">
                                                    <span>Newegg B2C View</span>
                                                    <span class="menu-arrow"></span>
                                                </a>
                                                <div class="collapse" id="neweggb2cSubmenu">
                                                    <ul class="side-nav-fourth-level">
                                                        @if (isset($permissions['newegg_b2c']) && in_array('view', $permissions['newegg_b2c']))
                                                            <li>
                                                                <a href="{{ route('neweggB2C') }}">Newegg B2C
                                                                    Analytics</a>
                                                            </li>
                                                        @endif
                                                        @if (isset($permissions['neweggb2c_zero_view']) && in_array('view', $permissions['neweggb2c_zero_view']))
                                                            <li>
                                                                <a href="{{ route('neweggb2c.zero.view') }}">Newegg
                                                                    B2C 0 view</a>
                                                            </li>
                                                        @endif
                                                        @if (isset($permissions['neweggb2c_low_visibility']) && in_array('view', $permissions['neweggb2c_low_visibility']))
                                                            <li>
                                                                <a
                                                                    href="{{ route('neweggb2c.low.visibility.view') }}">Newegg
                                                                    B2C Low Visibility</a>
                                                            </li>
                                                        @endif

                                                    </ul>
                                                </div>
                                            </li>
                                        @endif

                                        @if (isset($permissions['listing_Neweggb2c']) && in_array('view', $permissions['listing_Neweggb2c']))
                                            <li>
                                                <a href="{{ route('listing.neweggb2c') }}">Listing Newegg B2C</a>
                                            </li>
                                        @endif

                                        @if (isset($permissions['listing_audit_Neweggb2c']) && in_array('view', $permissions['listing_audit_Neweggb2c']))
                                            <li>
                                                <a href="{{ route('listing.audit.neweggb2c') }}">Listing Audit Newegg
                                                    B2C</a>
                                            </li>
                                        @endif


                                    </ul>
                                </div>
                            </li> -->

                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarwayfair" aria-expanded="false"
                                    aria-controls="sidebarwayfair">
                                    <span> Wayfair </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarwayfair">
                                    <ul class="side-nav-third-level">
                                        @if (
                                            (isset($permissions['wayfair']) && in_array('view', $permissions['wayfair'])) ||
                                                (isset($permissions['wayfair_zero_view']) && in_array('view', $permissions['wayfair_zero_view'])) ||
                                                (isset($permissions['wayfair_low_visibility']) && in_array('view', $permissions['wayfair_low_visibility'])))
                                            <li>
                                                <a data-bs-toggle="collapse" href="#wayfairSubmenu"
                                                    aria-expanded="false" aria-controls="wayfairSubmenu">
                                                    <span>Wayfair View</span>
                                                    <span class="menu-arrow"></span>
                                                </a>
                                                <div class="collapse" id="wayfairSubmenu">
                                                    <ul class="side-nav-fourth-level">
                                                        @if (isset($permissions['wayfair']) && in_array('view', $permissions['wayfair']))
                                                            <li>
                                                                <a href="{{ route('Wayfair') }}">Wayfair
                                                                    Analytics</a>
                                                            </li>
                                                        @endif
                                                        @if (isset($permissions['wayfair_zero_view']) && in_array('view', $permissions['wayfair_zero_view']))
                                                            <li>
                                                                <a href="{{ route('wayfair.zero.view') }}">Wayfair 0
                                                                    view</a>
                                                            </li>
                                                        @endif
                                                        @if (isset($permissions['wayfair_low_visibility']) && in_array('view', $permissions['wayfair_low_visibility']))
                                                            <li>
                                                                <a href="{{ route('wayfair.low.visibility.view') }}">Wayfair
                                                                    Low Visibility</a>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </li>
                                        @endif

                                            <li>
                                                <a href="{{ route('listing.wayfair') }}">Listing Wayfair</a>
                                            </li>

                                            <li>
                                                <a href="{{ route('listing.audit.wayfair') }}">Listing Audit
                                                    Wayfair</a>
                                            </li>


                                    </ul>
                                </div>
                            </li>

                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarReverb" aria-expanded="false"
                                    aria-controls="sidebarReverb">
                                    <span> Reverb </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarReverb">
                                    <ul class="side-nav-third-level">
                                       
                                            <li>
                                                <a data-bs-toggle="collapse" href="#reverbSubmenu"
                                                    aria-expanded="false" aria-controls="reverbSubmenu">
                                                    <span>Reverb View</span>
                                                    <span class="menu-arrow"></span>
                                                </a>
                                                <div class="collapse" id="reverbSubmenu">
                                                    <ul class="side-nav-fourth-level">
                                                            <li>
                                                                <a href="{{ route('reverb') }}">Reverb Analytics</a>
                                                            </li>

                                                         <li>
                                                                <a href="{{ url('reverb-pricing-cvr') }}">Reverb Pricing CVR</a>
                                                            </li>
                                                            <li>
                                                                <a href="{{ route('reverb.zero.view') }}">Reverb 0
                                                                    view</a>
                                                            </li>
                                                            <li>
                                                                <a href="{{ route('reverb.low.visibility.view') }}">Reverb
                                                                    Low Visibility</a>
                                                            </li>
                                                    </ul>
                                                </div>
                                            </li>

                                            <li>
                                                <a href="{{ route('listing.reverb') }}">Listing Reverb</a>
                                            </li>

                                            <li>
                                                <a href="{{ route('listing.audit.reverb') }}">Listing Audit
                                                    Reverb</a>
                                            </li>

   <li>
                                                <a href="{{ url('reverb-pricing-increase-cvr') }}">
                                                    Reverb Pricing Increase CVR</a>
                                            </li>
                                               <li>
                                                <a href="{{ url('reverb-pricing-decrease-cvr') }}">
                                                    Reverb Pricing Decrease CVR</a>
                                            </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarTemu" aria-expanded="false"
                                    aria-controls="sidebarTemu">
                                    <span> Temu </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarTemu">
                                    <ul class="side-nav-third-level">
                                        
                                            <li>
                                                <a data-bs-toggle="collapse" href="#temuSubmenu"
                                                    aria-expanded="false" aria-controls="temuSubmenu">
                                                    <span>Temu View</span>
                                                    <span class="menu-arrow"></span>
                                                </a>
                                                <div class="collapse" id="temuSubmenu">
                                                    <ul class="side-nav-fourth-level">
                                                            <li>
                                                                <a href="{{ route('temu') }}">Temu</a>
                                                            </li>
                                                            <li>
                                                                <a href="{{ route('temu.zero.view') }}">Temu 0
                                                                    view</a>
                                                            </li>
                                                            <li>
                                                                <a href="{{ route('temu.low.visibility.view') }}">Temu
                                                                    Low Visibility</a>
                                                            </li>
                                                        @endif

                                                        <li>
                                                            <a href="{{ url('temu-pricing-cvr') }}">Temu Pricing
                                                                CVR</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </li>
                                        @endif

                                        <li>
                                            <a href="{{ route('temu.pricing.inc') }}"> Temu Pricing Increase CVR</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('temu.pricing.dsc') }}"> Temu Pricing Decrease CVR</a>
                                        </li>

                                        @if (isset($permissions['listing_temu']) && in_array('view', $permissions['listing_temu']))
                                            <li>
                                                <a href="{{ route('listing.temu') }}">Listing Temu</a>
                                            </li>
                                        @endif

                                        @if (isset($permissions['listing_audit_temu']) && in_array('view', $permissions['listing_audit_temu']))
                                            <li>
                                                <a href="{{ route('listing.audit.temu') }}">Listing Audit Temu</a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </li>

                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarThirdLevel1" aria-expanded="false"
                                    aria-controls="sidebarThirdLevel">
                                    <span> Doba </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarThirdLevel1">
                                    <ul class="side-nav-third-level">
                                        @if (
                                            (isset($permissions['doba']) && in_array('view', $permissions['doba'])) ||
                                                (isset($permissions['doba_zero_view']) && in_array('view', $permissions['doba_zero_view'])) ||
                                                (isset($permissions['doba_low_visibility']) && in_array('view', $permissions['doba_low_visibility'])))
                                            <li>
                                                <a data-bs-toggle="collapse" href="#dobaSubmenu"
                                                    aria-expanded="false" aria-controls="dobaSubmenu">
                                                    <span>Doba View</span>
                                                    <span class="menu-arrow"></span>
                                                </a>
                                                <div class="collapse" id="dobaSubmenu">
                                                    <ul class="side-nav-fourth-level">
                                                        @if (isset($permissions['doba']) && in_array('view', $permissions['doba']))
                                                            <li>
                                                                <a href="{{ route('doba') }}">Doba's Analytics</a>
                                                            </li>
                                                        @endif

                                                        @if (isset($permissions['doba_zero_view']) && in_array('view', $permissions['doba_zero_view']))
                                                            <li>
                                                                <a href="{{ route('zero.doba') }}">Doba 0 view</a>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </li>
                                        @endif

                                        @if (isset($permissions['listing_doba']) && in_array('view', $permissions['listing_doba']))
                                            <li>
                                                <a href="{{ route('listing.doba') }}">Listing Doba</a>
                                            </li>
                                        @endif

                                        @if (isset($permissions['listing_audit_doba']) && in_array('view', $permissions['listing_audit_doba']))
                                            <li>
                                                <a href="#">Listing Audit doba</a>
                                            </li>
                                        @endif
                                        <li>
                                            <a href="{{ url('doba-pricing-cvr') }}">Doba Pricing - CVR</a>
                                        </li>


                                    </ul>
                                </div>
                            </li>

                            <!-- Ebay 2 -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarEbay2" aria-expanded="false"
                                    aria-controls="sidebarEbay2">
                                    <span> Ebay 2 </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarEbay2">
                                    <ul class="side-nav-third-level">
                                        <li>
                                            <a data-bs-toggle="collapse" href="#ebay2Submenu" aria-expanded="false"
                                                aria-controls="ebay2Submenu">
                                                <span>Ebay 2 View</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="ebay2Submenu">
                                                <ul class="side-nav-fourth-level">
                                                    <li>
                                                        <a href="{{ url('ebayTwoAnalysis') }}">Ebay 2's Analytics</a>
                                                    </li>
                                                    {{-- <li>
                                                        <a href="{{ route('zero.ebay2') }}">Ebay 2's 0 View</a>
                                                    </li> --}}
                                                 
                                                        <li>
                                                            <a href="{{ route('ebay2.low.visibility.view') }}">Ebay
                                                                2's Low Visibility</a>
                                                        </li>
                                                 

                                                  
                                                        <li>
                                                            <a href="{{ route('zero.ebay2') }}">Ebay 2's 0
                                                                view</a>
                                                        </li>
                                                
                                                </ul>
                                            </div>
                                        </li>

                                            <li>
                                                <a href="{{ route('listing.ebayTwo') }}">Listing Ebay 2</a>
                                            </li>

                                        <li>
                                            <a href="{{ url('ebayTwoPricingCVR') }}">Ebay 2 Pricing - CVR</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <!-- Ebay 3 -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarEbay3" aria-expanded="false"
                                    aria-controls="sidebarEbay3">
                                    <span> Ebay 3 </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarEbay3">
                                    <ul class="side-nav-third-level">
                                        <li>
                                            <a data-bs-toggle="collapse" href="#ebay3Submenu" aria-expanded="false"
                                                aria-controls="ebay3Submenu">
                                                <span>Ebay 3 View</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="ebay3Submenu">
                                                <ul class="side-nav-fourth-level">
                                                    <li>
                                                        <a href="{{ url('ebayThreeAnalysis') }}">Ebay 3's
                                                            Analytics</a>
                                                    </li>
                                                    {{-- <li>
                                                        <a href="{{ route('zero.ebay3') }}">Ebay 3's 0 View</a>
                                                    </li> --}}
                                                        <li>
                                                            <a href="{{ route('ebay3.low.visibility.view') }}">Ebay
                                                                3's Low Visibility</a>
                                                        </li>

                                                        <li>
                                                            <a href="{{ route('zero.ebay3') }}">Ebay 3's 0
                                                                view</a>
                                                        </li>
                                                </ul>
                                            </div>
                                        </li>

                                            <li>
                                                <a href="{{ route('listing.ebayThree') }}">Listing Ebay 3</a>
                                            </li>

                                        <li>
                                            <a href="{{ url('ebayThreePricingCVR') }}">Ebay 3 Pricing - CVR</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>


                            <!-- Walmart -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarWalmart" aria-expanded="false"
                                    aria-controls="sidebarWalmart">
                                    <span> Walmart </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarWalmart">
                                    <ul class="side-nav-third-level">
                                        <li>
                                            <a data-bs-toggle="collapse" href="#walmartSubmenu" aria-expanded="false"
                                                aria-controls="walmartSubmenu">
                                                <span>Walmart View</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="walmartSubmenu">
                                                <ul class="side-nav-fourth-level">
                                                    <li>
                                                        <a href="{{ url('walmartAnalysis') }}">Walmart's
                                                            Analytics</a>
                                                    </li>

                                                        <li>
                                                            <a href="{{ route('zero.walmart') }}">Walmart 0
                                                                view</a>
                                                        </li>
                                                </ul>
                                            </div>
                                        </li>

                                            <li>
                                                <a href="{{ route('listing.walmart') }}">Listing Walmart</a>
                                            </li>

                                        <li>
                                            <a href="walmartPricingCVR">Walmart Pricing - CVR</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <!-- Aliexpress -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarAliexpress" aria-expanded="false"
                                    aria-controls="sidebarAliexpress">
                                    <span>Aliexpress</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarAliexpress">
                                    <ul class="side-nav-third-level">
                                        <!-- <li><a href="#">Aliexpress Analytics</a></li> -->
                                        <li>
                                            <a href="{{ url('aliexpressAnalysis') }}">Aliexpress Analytics</a>
                                        </li>

                                        <li>
                                            <a href="{{ route('zero.aliexpress') }}">Aliexpress 0 view</a>
                                        </li>

                                            <li><a href="{{ route('listing.aliexpress') }}">Listing Aliexpress</a>
                                            </li>
                                    </ul>
                                </div>
                            </li>
                            <!-- eBay Variation -->
                            {{-- <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarEbayVariation" aria-expanded="false"
                                    aria-controls="sidebarEbayVariation">
                                    <span>eBay Variation</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarEbayVariation">
                                    <ul class="side-nav-third-level">
                                        <li><a href="#">eBay Variation Analytics</a></li>

                                        @if (isset($permissions['ebay_variation_zero_view']) && in_array('view', $permissions['ebay_variation_zero_view']))
                                            <li>
                                                <a href="{{ route('zero.ebayvariation') }}">eBay Variation 0 view</a>
                                            </li>
                                        @endif

                                        @if (isset($permissions['listing_ebayvariation']) && in_array('view', $permissions['listing_ebayvariation']))
                                            <li><a href="{{ route('listing.ebayvariation') }}">Listing eBay
                                                    Variation</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </li> --}}
                            <!-- Shopify wholesale/DS -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarShopifyWholesale" aria-expanded="false"
                                    aria-controls="sidebarShopifyWholesale">
                                    <span>Shopify wholesale/DS</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarShopifyWholesale">
                                    <ul class="side-nav-third-level">
                                        <li><a href="#">Shopify Wholesale Analytics</a></li>

                                            <li>
                                                <a href="{{ route('zero.shopifywholesale') }}">Shopify Wholesale 0
                                                    view</a>
                                            </li>

                                            <li><a href="{{ route('listing.shopifywholesale') }}">Listing Shopify
                                                    wholesale/DS</a></li>
                                    </ul>
                                </div>
                            </li>
                            <!-- Faire -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarFaire" aria-expanded="false"
                                    aria-controls="sidebarFaire">
                                    <span>Faire</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarFaire">
                                    <ul class="side-nav-third-level">
                                            <li><a href="{{ route('zero.faire') }}">Faire 0 view</a></li>

                                            <li><a href="{{ route('listing.faire') }}">Listing Faire</a></li>
                                    </ul>
                                </div>
                            </li>
                            <!-- Tiktok Shop -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarTiktokShop" aria-expanded="false"
                                    aria-controls="sidebarTiktokShop">
                                    <span>Tiktok Shop</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarTiktokShop">
                                    <ul class="side-nav-third-level">
                                        <li>
                                            <a href="{{ url('tiktokAnalysis') }}">Tiktok Shop Analytics</a>
                                        </li>

                                        <li>
                                            <a href="{{ route('zero.tiktokshop') }}">Tiktok Shop 0 view</a>
                                        </li>

                                            <li><a href="{{ route('listing.tiktokshop') }}">Listing Tiktok Shop</a>
                                            </li>
                                    </ul>
                                </div>
                            </li>
                            <!-- Mercari w Ship -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarMercariWShip" aria-expanded="false"
                                    aria-controls="sidebarMercariWShip">
                                    <span>Mercari w Ship</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarMercariWShip">
                                    <ul class="side-nav-third-level">
                                            <li>
                                                <a href="{{ route('zero.mercariwship') }}">Mercari w Ship 0 view</a>
                                            </li>

                                            <li><a href="{{ route('listing.mercariwship') }}">Listing Mercari w
                                                    Ship</a></li>
                                    </ul>
                                </div>
                            </li>
                            <!-- FB Marketplace -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarFBMarketplace" aria-expanded="false"
                                    aria-controls="sidebarFBMarketplace">
                                    <span>FB Marketplace</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarFBMarketplace">
                                    <ul class="side-nav-third-level">
                                            <li>
                                                <a href="{{ route('zero.fbmarketplace') }}">FB Marketplace 0
                                                    view</a>
                                            </li>

                                            <li><a href="{{ route('listing.fbmarketplace') }}">Listing FB
                                                    Marketplace</a></li>
                                    </ul>
                                </div>
                            </li>
                            <!-- Business 5Core -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarBusiness5Core" aria-expanded="false"
                                    aria-controls="sidebarBusiness5Core">
                                    <span>Business 5Core</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarBusiness5Core">
                                    <ul class="side-nav-third-level">
                                            <li>
                                                <a href="{{ route('zero.business5core') }}">Business 5Core 0
                                                    view</a>
                                            </li>

                                            <li><a href="{{ route('listing.business5core') }}">Listing Business
                                                    5Core</a></li>
                                    </ul>
                                </div>
                            </li>
                            <!-- PLS -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarPLS" aria-expanded="false"
                                    aria-controls="sidebarPLS">
                                    <span>PLS</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarPLS">
                                    <ul class="side-nav-third-level">
                                            <li>
                                                <a href="{{ route('zero.pls') }}">PLS 0 view</a>
                                            </li>

                                            <li><a href="{{ route('listing.pls') }}">Listing PLS</a></li>
                                    </ul>
                                </div>
                            </li>

                            <!-- Auto DS -->
                            <!-- <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarAutoDS" aria-expanded="false"
                                    aria-controls="sidebarAutoDS">
                                    <span>Auto DS</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarAutoDS">
                                    <ul class="side-nav-third-level">
                                        @if (isset($permissions['autods_zero_view']) && in_array('view', $permissions['autods_zero_view']))
                                            <li>
                                                <a href="{{ route('zero.autods') }}">Auto DS 0 view</a>
                                            </li>
                                        @endif

                                        @if (isset($permissions['listing_autods']) && in_array('view', $permissions['listing_autods']))
                                            <li><a href="{{ route('listing.autods') }}">Listing Auto DS</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </li> -->

                            <!-- Mercari w/o Ship -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarMercariWoShip" aria-expanded="false"
                                    aria-controls="sidebarMercariWoShip">
                                    <span>Mercari w/o Ship</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarMercariWoShip">
                                    <ul class="side-nav-third-level">
                                            <li>
                                                <a href="{{ route('zero.mercariwoship') }}">Mercari w/o Ship 0
                                                    view</a>
                                            </li>

                                            <li><a href="{{ route('listing.mercariwoship') }}">Listing Mercari w/o
                                                    Ship</a></li>
                                    </ul>
                                </div>
                            </li>

                            <!-- Poshmark -->
                            <!-- <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarPoshmark" aria-expanded="false"
                                    aria-controls="sidebarPoshmark">
                                    <span>Poshmark</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarPoshmark">
                                    <ul class="side-nav-third-level">
                                        @if (isset($permissions['poshmark_zero_view']) && in_array('view', $permissions['poshmark_zero_view']))
                                            <li>
                                                <a href="{{ route('zero.poshmark') }}">Poshmark 0 view</a>
                                            </li>
                                        @endif

                                        @if (isset($permissions['listing_poshmark']) && in_array('view', $permissions['listing_poshmark']))
                                            <li><a href="{{ route('listing.poshmark') }}">Listing Poshmark</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </li> -->

                            <!-- Tiendamia -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarTiendamia" aria-expanded="false"
                                    aria-controls="sidebarTiendamia">
                                    <span>Tiendamia</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarTiendamia">
                                    <ul class="side-nav-third-level">
                                            <li>
                                                <a href="{{ route('zero.tiendamia') }}">Tiendamia 0 view</a>
                                            </li>

                                            <li><a href="{{ route('listing.tiendamia') }}">Listing Tiendamia</a>
                                            </li>
                                    </ul>
                                </div>
                            </li>
                            <!-- Shein -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarShein" aria-expanded="false"
                                    aria-controls="sidebarShein">
                                    <span>Shein</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarShein">
                                    <ul class="side-nav-third-level">
                                        <li>
                                            <a href="{{ url('sheinAnalysis') }}">Shein
                                                Analytics</a>
                                        </li>
                                            <li><a href="{{ route('zero.shein') }}">Shein 0 view</a></li>
                                            <li><a href="{{ route('listing.shein') }}">Listing Shein</a></li>
                                    </ul>
                                </div>
                            </li>

                            <!-- Spocket -->
                            <!-- <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarSpocket" aria-expanded="false"
                                    aria-controls="sidebarSpocket">
                                    <span>Spocket</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarSpocket">
                                    <ul class="side-nav-third-level">
                                        @if (isset($permissions['spocket_zero_view']) && in_array('view', $permissions['spocket_zero_view']))
                                            <li><a href="{{ route('zero.spocket') }}">Spocket 0 view</a></li>
                                        @endif
                                        @if (isset($permissions['listing_spocket']) && in_array('view', $permissions['listing_spocket']))
                                            <li><a href="{{ route('listing.spocket') }}">Listing Spocket</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </li> -->

                            <!-- Zendrop -->
                            <!-- <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarZendrop" aria-expanded="false"
                                    aria-controls="sidebarZendrop">
                                    <span>Zendrop</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarZendrop">
                                    <ul class="side-nav-third-level">
                                        @if (isset($permissions['zendrop_zero_view']) && in_array('view', $permissions['zendrop_zero_view']))
                                            <li><a href="{{ route('zero.zendrop') }}">Zendrop 0 view</a></li>
                                        @endif

                                        @if (isset($permissions['listing_zendrop']) && in_array('view', $permissions['listing_zendrop']))
                                            <li><a href="{{ route('listing.zendrop') }}">Listing Zendrop</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </li> -->

                            <!-- Syncee -->
                            <!-- <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarSyncee" aria-expanded="false"
                                    aria-controls="sidebarSyncee">
                                    <span>Syncee</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarSyncee">
                                    <ul class="side-nav-third-level">
                                        @if (isset($permissions['syncee_zero_view']) && in_array('view', $permissions['syncee_zero_view']))
                                            <li><a href="{{ route('zero.syncee') }}">Syncee 0 view</a></li>
                                        @endif

                                        @if (isset($permissions['listing_syncee']) && in_array('view', $permissions['listing_syncee']))
                                            <li><a href="{{ route('listing.syncee') }}">Listing Syncee</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </li> -->

                            <!-- Offerup -->
                            <!-- <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarOfferup" aria-expanded="false"
                                    aria-controls="sidebarOfferup">
                                    <span>Offerup</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarOfferup">
                                    <ul class="side-nav-third-level">
                                        @if (isset($permissions['offerup_zero_view']) && in_array('view', $permissions['offerup_zero_view']))
                                            <li><a href="{{ route('zero.offerup') }}">Offerup 0 view</a></li>
                                        @endif

                                        @if (isset($permissions['listing_offerup']) && in_array('view', $permissions['listing_offerup']))
                                            <li><a href="{{ route('listing.offerup') }}">Listing Offerup</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </li> -->
                            <!-- Newegg B2B -->
                            <!-- <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarNeweggB2B" aria-expanded="false"
                                    aria-controls="sidebarNeweggB2B">
                                    <span>Newegg B2B</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarNeweggB2B">
                                    <ul class="side-nav-third-level">
                                        @if (isset($permissions['neweggb2b_zero_view']) && in_array('view', $permissions['neweggb2b_zero_view']))
                                            <li>
                                                <a href="{{ route('zero.neweggb2b') }}">Newegg B2B 0 view</a>
                                            </li>
                                        @endif
                                        @if (isset($permissions['listing_neweggb2b']) && in_array('view', $permissions['listing_neweggb2b']))
                                            <li><a href="{{ route('listing.neweggb2b') }}">Listing Newegg B2B</a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </li> -->
                            <!-- Appscenic -->
                            <!-- <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarAppscenic" aria-expanded="false"
                                    aria-controls="sidebarAppscenic">
                                    <span>Appscenic</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarAppscenic">
                                    <ul class="side-nav-third-level">
                                        @if (isset($permissions['appscenic_zero_view']) && in_array('view', $permissions['appscenic_zero_view']))
                                            <li><a href="{{ route('zero.appscenic') }}">Appscenic 0 view</a></li>
                                        @endif

                                        @if (isset($permissions['listing_appscenic']) && in_array('view', $permissions['listing_appscenic']))
                                            <li><a href="{{ route('listing.appscenic') }}">Listing Appscenic</a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </li> -->

                            <!-- FB Shop -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarFBShop" aria-expanded="false"
                                    aria-controls="sidebarFBShop">
                                    <span>FB Shop</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarFBShop">
                                    <ul class="side-nav-third-level">
                                            <li>
                                                <a href="{{ route('zero.fbshop') }}">FB Shop 0 view</a>
                                            </li>

                                            <li><a href="{{ route('listing.fbshop') }}">Listing FB Shop</a></li>
                                    </ul>
                                </div>
                            </li>
                            <!-- Instagram Shop -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarInstagramShop" aria-expanded="false"
                                    aria-controls="sidebarInstagramShop">
                                    <span>Instagram Shop</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarInstagramShop">
                                    <ul class="side-nav-third-level">
                                            <li>
                                                <a href="{{ route('zero.instagramshop') }}">Instagram Shop 0
                                                    view</a>
                                            </li>

                                            <li><a href="{{ route('listing.instagramshop') }}">Listing Instagram
                                                    Shop</a></li>
                                    </ul>
                                </div>
                            </li>
                            <!-- Yamibuy -->
                            <!-- <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarYamibuy" aria-expanded="false"
                                    aria-controls="sidebarYamibuy">
                                    <span>Yamibuy</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarYamibuy">
                                    <ul class="side-nav-third-level">
                                        @if (isset($permissions['yamibuy_zero_view']) && in_array('view', $permissions['yamibuy_zero_view']))
                                            <li><a href="{{ route('zero.yamibuy') }}">Yamibuy 0 view</a></li>
                                        @endif

                                        @if (isset($permissions['listing_yamibuy']) && in_array('view', $permissions['listing_yamibuy']))
                                            <li><a href="{{ route('listing.yamibuy') }}">Listing Yamibuy</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </li> -->
                            <!-- DHGate -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarDHGate" aria-expanded="false"
                                    aria-controls="sidebarDHGate">
                                    <span>DHGate</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarDHGate">
                                    <ul class="side-nav-third-level">
                                     
                                            <li><a href="{{ route('zero.dhgate') }}">DHGate 0 view</a></li>
                                       

                                      
                                            <li><a href="{{ route('listing.dhgate') }}">Listing DHGate</a></li>
                                     
                                    </ul>
                                </div>
                            </li>
                            <!-- Bestbuy USA -->
                            <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarBestbuyUSA" aria-expanded="false"
                                    aria-controls="sidebarBestbuyUSA">
                                    <span>Bestbuy USA</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarBestbuyUSA">
                                    <ul class="side-nav-third-level">
                                    
                                            <li><a href="{{ route('zero.bestbuyusa') }}">Bestbuy USA 0 view</a></li>
                                      

                                      
                                            <li><a href="{{ route('listing.bestbuyusa') }}">Listing Bestbuy USA</a>
                                            </li>
                                       
                                    </ul>
                                </div>
                            </li>
                            <!-- SW Gear Exchange -->
                            <!-- <li class="side-nav-item">
                                <a data-bs-toggle="collapse" href="#sidebarSWGearExchange" aria-expanded="false"
                                    aria-controls="sidebarSWGearExchange">
                                    <span>SW Gear Exchange</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarSWGearExchange">
                                    <ul class="side-nav-third-level">
                                        @if (isset($permissions['swgearexchange_zero_view']) && in_array('view', $permissions['swgearexchange_zero_view']))
                                            <li><a href="{{ route('zero.swgearexchange') }}">SW Gear Exchange 0
                                                    view</a></li>
                                        @endif

                                        @if (isset($permissions['listing_swgearexchange']) && in_array('view', $permissions['listing_swgearexchange']))
                                            <li><a href="{{ route('listing.swgearexchange') }}">Listing SW Gear
                                                    Exchange</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </li> -->
                        </ul>
                    </div>
                </li>
            @endif
        </ul>
        <!--- End Sidemenu -->

        <div class="clearfix"></div>
    </div>
</div>
<!-- ========== Left Sidebar End ========== -->
