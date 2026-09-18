<?php
/**
 * Pagination - Show numbered pagination for catalog pages
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/pagination.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$total   = isset( $total ) ? $total : wc_get_loop_prop( 'total_pages' );
$current = isset( $current ) ? $current : wc_get_loop_prop( 'current_page' );
$base = isset( $base )
    ? $base
    : esc_url_raw(
        str_replace(
            999999999,
            '%#%',
            remove_query_arg(
                'add-to-cart',
                get_pagenum_link( 999999999, false )
            )
        )
    );
$format  = isset( $format ) ? $format : '';


$previous_page_url = str_replace(
    '%#%',
    max(1, $current - 1),
    $base
);

$next_page_url = str_replace(
    '%#%',
    min($total, $current + 1),
    $base
);


if ( $total > 1 ) : ?>
    <nav class="woocommerce-pagination">

        <?php  $current === $total ? $disabled = 'disabled' : $disabled = ''; ?>

            <button
                    id="load-more"
                    class="catalog-load-more <?php echo $disabled ?>"
                    type="button"
                    data-current-page="<?php echo esc_attr( $current ); ?>"
                    data-max-pages="<?php echo esc_attr( $total ); ?>"
                    data-next-page="<?php echo esc_url( get_pagenum_link( $current + 1 ) ); ?>"
                    data-page-base="<?php echo esc_attr( $base ); ?>"
            >
                <span>Завантажити ще</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="8" viewBox="0 0 11 8" fill="none">
                    <path d="M1 1L5.5 6L10 1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>

        <div class="pagination-container">


            <!-- предыдущая стрелка -->
            <?php if ( $current > 1 ) : ?>
                <a
                        class="prev page-arrow"
                        href="<?php echo esc_url( $previous_page_url ); ?>"
                        aria-label="Попередня сторінка"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="15" viewBox="0 0 21 15" fill="none">
                        <path d="M0.376364 8.34928C-0.63263 6.87833 0.624884 6.28357 1.37045 5.59323C3.76619 3.99483 5.22251 2.61414 6.98701 0.904222L6.91247 0.978574C7.62324 0.0439599 8.50797 -0.349011 9.49212 0.373191C10.5359 1.13788 10.2675 2.19463 9.78535 3.19828C9.06961 4.68516 9.73067 5.08875 11.023 5.0622C13.1652 5.01972 15.3075 5.13654 17.4448 4.99316C19.6119 4.84979 20.8843 5.65163 20.9936 7.22348C21.103 8.73692 19.7958 9.93174 17.7579 10.0804C15.387 10.2504 12.9913 9.8096 10.5259 10.3937C11.1174 11.1372 11.6343 11.7957 11.7387 12.6825C11.7387 12.9002 11.7387 13.1232 11.7387 13.341C11.0975 15.2633 9.81021 15.2421 8.32903 14.6367C7.98607 14.4508 7.63813 14.265 7.29517 14.0791C4.85967 12.3532 2.14584 10.9991 0.381339 8.34397L0.376364 8.34928Z" fill="currentColor"/>
                    </svg>
                </a>
            <?php else : ?>
                <span class="prev page-arrow disabled" aria-disabled="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="21" height="15" viewBox="0 0 21 15" fill="none">
                          <path d="M0.376364 8.34928C-0.63263 6.87833 0.624884 6.28357 1.37045 5.59323C3.76619 3.99483 5.22251 2.61414 6.98701 0.904222L6.91247 0.978574C7.62324 0.0439599 8.50797 -0.349011 9.49212 0.373191C10.5359 1.13788 10.2675 2.19463 9.78535 3.19828C9.06961 4.68516 9.73067 5.08875 11.023 5.0622C13.1652 5.01972 15.3075 5.13654 17.4448 4.99316C19.6119 4.84979 20.8843 5.65163 20.9936 7.22348C21.103 8.73692 19.7958 9.93174 17.7579 10.0804C15.387 10.2504 12.9913 9.8096 10.5259 10.3937C11.1174 11.1372 11.6343 11.7957 11.7387 12.6825C11.7387 12.9002 11.7387 13.1232 11.7387 13.341C11.0975 15.2633 9.81021 15.2421 8.32903 14.6367C7.98607 14.4508 7.63813 14.265 7.29517 14.0791C4.85967 12.3532 2.14584 10.9991 0.381339 8.34397L0.376364 8.34928Z" fill="currentColor"/>
                        </svg>
                    </span>
            <?php endif; ?>


            <!-- номера страниц -->
            <?php
            $page_links = paginate_links(
                array(
                    'base'      => $base,
                    'format'    => $format,
                    'current'   => max( 1, $current ),
                    'total'     => $total,
                    'prev_next' => false,
                    'end_size'  => 1,
                    'mid_size'  => 1,
                    'type'      => 'array',
                )
            );

            if ( $page_links ) : ?>
                <ul class="page-numbers-container">
                    <?php
                    foreach ( $page_links as $page_link ) :
                        ?>
                        <li><?php echo wp_kses_post( $page_link ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <!-- следующая стрелка -->
            <?php if ( $current < $total ) : ?>
                <a
                        class="next page-arrow"
                        href="<?php echo esc_url( $next_page_url ); ?>"
                        aria-label="Наступна сторінка"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="15" viewBox="0 0 21 15" fill="none">
                        <path d="M20.6236 6.65072C21.6326 8.12167 20.3751 8.71643 19.6296 9.40677C17.2338 11.0052 15.7775 12.3859 14.013 14.0958L14.0875 14.0214C13.3768 14.956 12.492 15.349 11.5079 14.6268C10.4641 13.8621 10.7325 12.8054 11.2146 11.8017C11.9304 10.3148 11.2693 9.91125 9.97702 9.9378C7.83477 9.98028 5.6925 9.86346 3.55522 10.0068C1.38812 10.1502 0.115719 9.34837 0.00636864 7.77652C-0.10298 6.26308 1.20424 5.06826 3.24211 4.91957C5.613 4.74964 8.00873 5.1904 10.4741 4.60626C9.88258 3.86282 9.36566 3.20434 9.26128 2.31752C9.26128 2.0998 9.26128 1.87676 9.26128 1.65903C9.90246 -0.263296 11.1898 -0.242057 12.671 0.363318C13.0139 0.549179 13.3619 0.735049 13.7048 0.92091C16.1403 2.64676 18.8542 4.00088 20.6187 6.65603L20.6236 6.65072Z" fill="currentColor"/>
                    </svg>
                </a>
            <?php else : ?>
                <span class="next page-arrow disabled" aria-disabled="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="21" height="15" viewBox="0 0 21 15" fill="none">
                          <path d="M20.6236 6.65072C21.6326 8.12167 20.3751 8.71643 19.6296 9.40677C17.2338 11.0052 15.7775 12.3859 14.013 14.0958L14.0875 14.0214C13.3768 14.956 12.492 15.349 11.5079 14.6268C10.4641 13.8621 10.7325 12.8054 11.2146 11.8017C11.9304 10.3148 11.2693 9.91125 9.97702 9.9378C7.83477 9.98028 5.6925 9.86346 3.55522 10.0068C1.38812 10.1502 0.115719 9.34837 0.00636864 7.77652C-0.10298 6.26308 1.20424 5.06826 3.24211 4.91957C5.613 4.74964 8.00873 5.1904 10.4741 4.60626C9.88258 3.86282 9.36566 3.20434 9.26128 2.31752C9.26128 2.0998 9.26128 1.87676 9.26128 1.65903C9.90246 -0.263296 11.1898 -0.242057 12.671 0.363318C13.0139 0.549179 13.3619 0.735049 13.7048 0.92091C16.1403 2.64676 18.8542 4.00088 20.6187 6.65603L20.6236 6.65072Z" fill="currentColor"/>
                        </svg>
                    </span>
            <?php endif; ?>



        </div>
    </nav>
<?php endif; ?>
