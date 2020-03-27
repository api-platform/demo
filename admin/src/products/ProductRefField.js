import React from 'react';
import { Link } from 'react-router-dom';

const ProductRefField = ({ record }) =>
    record ? (
        <Link to={`products/${record.id}`}>{record.reference}</Link>
    ) : null;

ProductRefField.defaultProps = {
    source: 'id',
    label: 'Reference',
};

export default ProductRefField;
