import React from 'react';
import {Route} from 'react-router-dom';
import {List} from '../components/book/';

export default [
  <Route path='/books/:page?' component={List} strict={true} key='list'/>,
];
